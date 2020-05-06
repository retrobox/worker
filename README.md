# Worker

Worker is the php script who listen and consume event on the [Jobatator](https://github.com/lefuturiste/jobatator) queue.

[![CircleCI](https://circleci.com/gh/retrobox/worker.svg?style=svg)](https://circleci.com/gh/retrobox/worker)

## Env vars

> see in .env.example

## Events

`order.payed`:
- generate an html billing
- send a billing email to the customer
- send a discord webhook

`order.shipped`:
- generate an html successful shipped email
- send a shipped email to the customer

`test.email`:
- send the test email (only for testing purpose)

## Docker image

`retrobox/worker`
