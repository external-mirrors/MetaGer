{{/*
Expand the name of the chart.
*/}}
{{- define "chart.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
If release name contains chart name it will be used as a full name.
*/}}
{{- define "chart.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "chart.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define "chart.labels" -}}
helm.sh/chart: {{ include "chart.chart" . }}
{{ include "chart.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
app: {{ .Release.Name }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "chart.selectorLabels" -}}
app.kubernetes.io/name: {{ .Release.Name }}-app
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{- define "chart.workerLabels" -}}
helm.sh/chart: {{ include "chart.chart" . }}
{{ include "chart.selectorLabelsWorker" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
app: {{ .Release.Name }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "chart.selectorLabelsWorker" -}}
app.kubernetes.io/name: {{ .Release.Name }}-worker
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{/*
Create the name of the service account to use
*/}}
{{- define "chart.serviceAccountName" -}}
{{- if .Values.serviceAccount.create }}
{{- default (include "chart.fullname" .) .Values.serviceAccount.name }}
{{- else }}
{{- default "default" .Values.serviceAccount.name }}
{{- end }}
{{- end }}

{{- define "fpm_image" -}}
{{- if eq .Values.image.fpm.tag "" -}}
{{- .Values.image.fpm.repository -}}
{{- else -}}
{{- printf "%s:%s" .Values.image.fpm.repository .Values.image.fpm.tag -}}
{{- end -}}
{{- end -}}

{{- define "nginx_image" -}}
{{- if eq .Values.image.nginx.tag "" -}}
{{- .Values.image.nginx.repository -}}
{{- else -}}
{{- printf "%s:%s" .Values.image.nginx.repository .Values.image.nginx.tag -}}
{{- end -}}
{{- end -}}

{{- define "secret_name" -}}
{{- printf "%s" .Release.Name }}
{{- end -}}

{{/*
Name used to address the valkey subchart's resources. Must match whatever is
passed as valkey.fullnameOverride so the two stay in sync; falls back to the
subchart's own default naming scheme when no override is given (e.g. plain
`helm install` without the CI script).

Truncated to 48, not 63: the subchart appends up to "-prestop-script" (15) to
this name without re-truncating, and 48 + 15 is exactly the 63-char cap on a
Kubernetes object name.
*/}}
{{- define "chart.valkeyFullname" -}}
{{- default (printf "%s-valkey" .Release.Name) .Values.valkeyName | trunc 48 | trimSuffix "-" }}
{{- end }}
{{/*
Environment shared by every MetaGer container.

Pass `appUrl: true` for the containers that need APP_URL — fpm and the scheduler
generate absolute URLs; the queue, reverb and fetcher workers never do.

    env:
    {{- include "chart.env" (dict "root" . "appUrl" true) | nindent 10 }}
*/}}
{{- define "chart.env" -}}
{{- $root := .root -}}
- name: APP_ENV
  value: {{ $root.Values.environment }}
{{- if .appUrl }}
- name: APP_URL
  value: {{ $root.Values.app_url }}
{{- end }}
{{/*
  The `default` connection. Must resolve to whichever pod is currently master —
  most of the app writes through this connection, and a replica answers a write
  with -READONLY. `-master` is the HAProxy master-proxy Service, which is why
  valkey.sentinel.masterProxy.enabled is not optional (see values.yaml).
*/}}
- name: REDIS_HOST
  value: {{ include "chart.valkeyFullname" $root }}-master.{{ $root.Release.Namespace }}.svc.cluster.local
- name: REDIS_PASSWORD
  valueFrom:
    secretKeyRef:
      name: {{ include "chart.valkeyFullname" $root }}
      key: password
{{/*
  The sentinel-aware connection (config/database.php: predis, replication =>
  sentinel). All three passwords come from the same secret key: the subchart
  gives Sentinel the same password it gives Valkey, for both `sentinel auth-pass`
  and Sentinel's own `requirepass`. The retired chart minted two.
*/}}
- name: REDIS_SENTINEL_HOST
  value: {{ include "chart.valkeyFullname" $root }}-sentinel.{{ $root.Release.Namespace }}.svc.cluster.local
- name: REDIS_SENTINEL_PASSWORD
  valueFrom:
    secretKeyRef:
      name: {{ include "chart.valkeyFullname" $root }}
      key: password
- name: REDIS_SENTINEL_REDIS_PASSWORD
  valueFrom:
    secretKeyRef:
      name: {{ include "chart.valkeyFullname" $root }}
      key: password
- name: REDIS_SENTINEL_SERVICE
  value: {{ $root.Values.valkey.sentinel.masterName }}
- name: REDIS_PORT
  value: "6379"
{{- if gt (len $root.Values.ingress.hosts) 0 }}
{{- with (index $root.Values.ingress.hosts 0) }}
- name: REVERB_HOST
  value: {{ .host }}
{{- end }}
{{- end }}
{{- range $name, $value := $root.Values.env }}
- name: {{ $name }}
  value: {{ $value | quote }}
{{- end }}
{{- end -}}

{{/*
Pod-level volumes. Every MetaGer pod mounts the same four.
*/}}
{{- define "chart.volumes" -}}
- name: secrets
  secret:
    secretName: {{ template "secret_name" . }}
- name: mglogs-persistent-storage
  persistentVolumeClaim:
    claimName: mglogs
- name: sqlite-databases
  emptyDir: {}
- name: fast-logs
  emptyDir: {}
{{- end -}}

{{/*
The .env file. Every container needs this one and nothing runs without it.
*/}}
{{- define "chart.mounts.env" -}}
- name: secrets
  mountPath: /metager/metager_app/.env
  subPath: ENV_PRODUCTION
  readOnly: true
{{- end -}}

{{/*
Search configuration and blacklists — only the containers that run a search need
these: fpm serves result pages, the queue runs jobs that touch the same config.
*/}}
{{- define "chart.mounts.config" -}}
- name: secrets
  mountPath: /metager/metager_app/config/sumas.json
  subPath: SUMAS_JSON
- name: secrets
  mountPath: /metager/metager_app/config/suggestions.json
  subPath: SUGGESTIONS
- name: secrets
  mountPath: /metager/metager_app/config/blacklistDomains.txt
  subPath: BLACKLIST_DOMAINS
- name: secrets
  mountPath: /metager/metager_app/config/blacklistUrl.txt
  subPath: BLACKLIST_URL
- name: secrets
  mountPath: /metager/metager_app/config/blacklistDescriptionUrl.txt
  subPath: BLACKLIST_DESCRIPTION_URL
{{- end -}}

{{/*
Writable storage: the shared log volume, the sqlite databases and the fast-log
scratch dir.
*/}}
{{- define "chart.mounts.storage" -}}
- name: mglogs-persistent-storage
  mountPath: /metager/metager_app/storage/metager
  readOnly: false
- name: sqlite-databases
  mountPath: /metager/metager_app/database/databases
- name: fast-logs
  mountPath: /metager/metager_app/storage/metager/fast_dir
{{- end -}}

{{/*
Creates each pod's own database.sqlite before its main container starts.

`sqlite-databases` is an emptyDir, not shared storage — each pod gets its own,
empty on every (re)start. The app Deployment gets a populated one for free
because it runs the image's normal entrypoint (`migrate --force`, among other
things). The scheduler and queue Deployments mount the same volume but override
`command`/`args` directly to invoke their artisan command, which replaces the
image's ENTRYPOINT rather than running alongside it - so nothing ever created
their copy of the file, and every local write (QueryLogger, the `logs_partitioned`
table `logs:gather` drains into) threw SQLiteDatabaseDoesNotExistException.

Needs `chart.env` passed through, same as the container it precedes gets -
without it this container has no DB_CONNECTION, so it falls through to
whatever the mounted `.env` secret's own default connection is. In this
chart that default is `pgsql`, a real shared database: migrate --force ran
against that instead of the local sqlite file, found it already fully
migrated, and reported "Nothing to migrate" - silently, with exit 0, having
touched neither the sqlite file nor (fortunately, this time) anything in
Postgres that wasn't already applied. `touch` first is kept anyway, now
harmless: once the connection is actually sqlite, migrate's own
createMissingSqliteDatabase() would create the file just as reliably, but
being explicit costs nothing. Relative to database/databases/ because that's
Docker's WORKDIR, unaffected by the command/args override below.

    initContainers:
    {{- include "chart.migrateInitContainer" (dict "root" . "appUrl" true) | nindent 8 }}
*/}}
{{- define "chart.migrateInitContainer" -}}
{{- $root := .root -}}
- name: migrate
  image: "{{ template "fpm_image" $root }}"
  command: ["/bin/sh", "-c"]
  args:
    - touch database/databases/database.sqlite && php artisan migrate --force
  env:
  {{- include "chart.env" (dict "root" $root "appUrl" .appUrl) | nindent 2 }}
  volumeMounts:
  {{- include "chart.mounts.env" $root | nindent 2 }}
  {{- include "chart.mounts.storage" $root | nindent 2 }}
{{- end -}}

{{/*
Labels for a component with its own Deployment.

    {{- include "chart.componentLabels" (dict "root" . "component" "scheduler") }}

The app and worker Deployments predate this and keep their own hand-written
helpers so their selector labels — which are immutable on a live Deployment —
do not change.
*/}}
{{- define "chart.componentSelectorLabels" -}}
app.kubernetes.io/name: {{ .root.Release.Name }}-{{ .component }}
app.kubernetes.io/instance: {{ .root.Release.Name }}
{{- end }}

{{- define "chart.componentLabels" -}}
helm.sh/chart: {{ include "chart.chart" .root }}
{{ include "chart.componentSelectorLabels" . }}
{{- if .root.Chart.AppVersion }}
app.kubernetes.io/version: {{ .root.Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .root.Release.Service }}
app: {{ .root.Release.Name }}
app.kubernetes.io/component: {{ .component }}
{{- end }}
