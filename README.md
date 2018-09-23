# Worker

Worker is the php script who listen and consume event on the RabbitMQ queue.

## Env vars

> see in .env.example

## Events

`order.payed`:
- generate an html billing
- send a billing email to the customer
- send a discord webhook

## Docker image

`retrobox/worker`