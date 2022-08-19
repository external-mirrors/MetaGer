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
app.kubernetes.io/name: {{ .Release.Name }}
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