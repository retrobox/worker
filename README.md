# Worker

Worker is the php script who listen and consume event on the RabbitMQ queue.

## Env vars

- `RABBITMQ_HOST`
- `RABBITMQ_PORT`
- `RABBITMQ_USERNAME`
- `RABBITMQ_PASSWORD`
- `RABBITMQ_VIRTUAL_HOST`


- `API_ENDPOINT`
- `API_KEY`


- `DISCORD_WEBHOOK_ORDER`


- `FTP_HOST`
- `FTP_PORT`
- `FTP_SSL`
- `FTP_USERNAME`
- `FTP_PASSWORD`
- `FTP_DIRECTORY`

## Events

`order.payed`:
- generate an html billing
- send a billing email to the customer
- send a discord webhook

## Docker image

`retrobox/worker`