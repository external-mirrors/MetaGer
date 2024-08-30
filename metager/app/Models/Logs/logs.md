## MetaGer Logs API

Using the MetaGer Logs API you can use our Endpoint to use historical log data for your research. Please keep in mind that access to this API is only granted in special cases for research purposes only and under the conditions of your signed NDA.

### GET logs

Fetch logs from our database for the specified time range. All times are specified in `UTC`. Your account must have access to logs for the specified `start_date`. The `end_date` can however exceed the permitted time ranges.  
Please note that you can request at most a days worth of data per request (see below). Data newer than `5 Minutes` ago from the current time cannot be requested. `end_date` will automatically be modified to match those requirements if needed. We will return the modified `end_date` as as seperate HTTP response header (see below).

```plaintext
GET /logs/api
```
Supported HTTP Headers:
| Header Name       | Type          | Required          | Description                                                                       |
|-------------------|---------------|-------------------|-----------------------------------------------------------------------------------|
| Authorization     | Bearer Token  | Yes               | Authorizes your request. Value must be `Bearer <TOKEN>`                           |
| Accepts           | Mime-Type     | No                | Response Type: Currently only `text/csv` is allowed. Default: `text/csv`  |

Supported URL parameters:

| Parameter         | Type          | Required          | Description                                                                                                           |
|-------------------|---------------|-------------------|-----------------------------------------------------------------------------------------------------------------------|
| start_date        | DateTime      | Yes               | Start Date: `Y-mm-dd H:i:s`                                                                                        |
| end_date          | DateTime      | No                | End Date: `Y-mm-dd H:i:s` If not defined uses `start_date + 23:59:59`. Cannot be later than `start_date + 23:59:59` |
| order             | String        | No                | Order response data. Can be either `ascending` or `descending`                                                        |

If successful, returns a status code of `200` and the following response as plain `CSV` table without headers:

#### CSV
```csv
"YYYY-mm-dd H:i:s","<query>"
```

The CSV values will be delimited by `,`, entries are enclosed by `"`. Double quotes (`"`) in values will be escaped by another double quote as per `RFC-4180`. Entries are seperated by newlines.

Errors will always be returned as JSON response!

The following response Headers will also be returned:

| Header Name             | Type          | Description                                           |
|-------------------------|---------------|-------------------------------------------------------|
| X-Rate-Limit-Max        | Integer       | Maximum allowed requests                              |
| X-Rate-Limit-Current    | Integer       | Currently used requests                               |
| X-Rate-Limit-More-In    | Integer       | Number of seconds until more requests are available   |
| X-End-Date-Used         | DateTime      | The actual `end_date` used (Format: Y-m-d H:i:s)      |

Default rate limits are set to `60 requests / hour`

Example request:

```shell
curl --header "Authorization: Bearer <your_access_token>" --header "Accepts: application/json" \
  --url "https://metager.de/logs/api?start_date=2024-08-24%2000%3A00%3A00"
```