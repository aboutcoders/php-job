# abc-job

[![Build Status](https://travis-ci.org/aboutcoders/php-job.png?branch=master)](https://travis-ci.org/aboutcoders/php-job)

A PHP library for asynchronous distributed job processing using [php-enqueue](https://github.com/php-enqueue) as transport layer.

**Note: This project is still experimental!**

## Features

This library provides the following features:

* Asynchronous distributed processing of 
    * Job: a single job
    * Batch: multiple jobs that are processed in parallel
    * Sequence: multiple jobs processed in sequential order
    * Free composition of Job, Sequence, and Batch
* Status information about jobs
* Cancellation and restarting of jobs
* Scheduled processing of jobs (requires [AbcSchedulerBundle](https://github.com/aboutcoders/scheduler-bundle/blob/master/AbcSchedulerBundle.php) 2.x) 
* JSON REST-Api & PHP client library
* [OpenApi](https://www.openapis.org/) documentation

## Installation

```bash
composer require abc/job
```

## Demo

You can find a demo [here](https://gitlab.com/hasc/abc-job-demo/).

## License

The MIT License (MIT). Please see [License File](./LICENSE) for more information.
