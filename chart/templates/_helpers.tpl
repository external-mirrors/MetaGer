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

{{- define "redis_image" -}}
{{- if eq .Values.image.redis.tag "" -}}
{{- .Values.image.redis.repository -}}
{{- else -}}
{{- printf "%s:%s" .Values.image.redis.repository .Values.image.redis.tag -}}
{{- end -}}
{{- end -}}

{{- define "secret_name" -}}
{{- printf "%s" .Release.Name }}
{{- end -}}

{{/*
Name used to address the redis-sentinel subchart's resources. Must match
whatever is passed as redis-sentinel.fullnameOverride so the two stay in
sync; falls back to the subchart's own default naming scheme when no
override is given (e.g. plain `helm install` without the CI script).
*/}}
{{- define "chart.redisSentinelFullname" -}}
{{- default (printf "%s-redis-sentinel" .Release.Name) .Values.redisSentinelName | trunc 63 | trimSuffix "-" }}
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
- name: REDIS_HOST
  value: {{ include "chart.redisSentinelFullname" $root }}-redis.{{ $root.Release.Namespace }}.svc.cluster.local
- name: REDIS_PASSWORD
  valueFrom:
    secretKeyRef:
      name: {{ include "chart.redisSentinelFullname" $root }}
      key: REDIS_PASSWORD
- name: REDIS_SENTINEL_HOST
  value: {{ include "chart.redisSentinelFullname" $root }}.{{ $root.Release.Namespace }}.svc.cluster.local
- name: REDIS_SENTINEL_PASSWORD
  valueFrom:
    secretKeyRef:
      name: {{ include "chart.redisSentinelFullname" $root }}
      key: SENTINEL_PASSWORD
- name: REDIS_SENTINEL_REDIS_PASSWORD
  valueFrom:
    secretKeyRef:
      name: {{ include "chart.redisSentinelFullname" $root }}
      key: REDIS_PASSWORD
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
  value: {{ $value }}
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
  mountPath: /metager/metager_app/config/adBlacklistDomains.txt
  subPath: ADBLACKLIST_DOMAINS
- name: secrets
  mountPath: /metager/metager_app/config/adBlacklistUrl.txt
  subPath: ADBLACKLIST_URL
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
