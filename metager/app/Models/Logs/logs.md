## MetaGer Logs API

Using the MetaGer Logs API you can use our Endpoint to use historical log data for your research. Please keep in mind that access to this API is only granted in special cases for research purposes only and under the conditions of your signed NDA.

### GET logs

Fetch logs from our database for the specified time range. All times are specified in `UTC`

```plaintext
GET /logs/api
```
Supported HTTP Headers:
| Header Name       | Type          | Required          | Description                                                                       |
|-------------------|---------------|-------------------|-----------------------------------------------------------------------------------|
| Authorization     | Bearer Token  | Yes               | Authorizes your request. Value must be `Bearer <TOKEN>`                           |
| Accepts           | Mime-Type     | No                | Response Type: Can be any of `text/csv`, `application/json`. Default: `text/csv`  |

Supported URL parameters:

| Parameter         | Type          | Required          | Description                                                                                                           |
|-------------------|---------------|-------------------|-----------------------------------------------------------------------------------------------------------------------|
| start_date        | DateTime      | Yes               | Start Date: `Y-mm-dd H:i:s`                                                                                        |
| end_date          | DateTime      | No                | End Date: `Y-mm-dd H:i:s` If not defined uses `start_date + 23:59:59`. Cannot be more than `start_date + 23:59:59` |
| order             | String        | No                | Order response data. Can be either `ascending` or `descending`                                                        |

If successful, returns a status code of `200` and the following response as `JsonArray` or plain `CSV`table without headers:

#### JSON
```json
[
    [
        'date': 'YYYY-mm-dd H:i:s',
        'query': '<query>
    ]
]
```

#### CSV
```csv
"YYYY-mm-dd H:i:s","<query>"
```

Errors will always be returned as JSON response!

The following Headers will also be returned:

| Header Name             | Type          | Description                                           |
|-------------------------|---------------|-------------------------------------------------------|
| X-Rate-Limit-Max        | Integer       | Maximum allowed requests                              |
| X-Rate-Limit-Current    | Integer       | Currently used requests                               |
| X-Rate-Limit-AvailableIn| DateTime      | Number of seconds until more attempts are available   |

Default rate limits are set to `60 requests / hour`

Example request:

```shell
curl --header "Authorization: Bearer <your_access_token>" --header "Accepts: application/json" \
  --url "https://metager.de/logs/api?start_date=2024-08-24%2000%3A00%3A00"
```