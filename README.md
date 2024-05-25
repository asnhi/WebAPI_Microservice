# GameStore Web Project - Microservice with Clean Architecture using Restful API

![Travis (.org)](https://travis-ci.org/vietnam-devs/coolstore-microservices.svg?branch=master)
[![Price](https://img.shields.io/badge/price-FREE-0098f7.svg)](https://github.com/vietnam-devs/coolstore-microservices/blob/master/LICENSE)

Welcome to Laravel GameStore, a robust project built on the principles of Clean Architecture and Microservices, utilizing the RabbitMQ engine. It is designed to facilitate the development of powerful and easily maintainable applications.
This project is an extension of the Monolithic project [WebApi_Clean_Architecture](https://github.com/asnhi/WebApi_Clean_Architecture.git), restructured into Microservices to enhance scalability and modularity.

> This repository based on some of the old libraries. So be careful if you use it in your production environment!

<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#try-docker">Wanna try running on Docker</a></li>
      </ul>
    </li>
    <li>
      <a href="#usage">Usage</a>
      <ul>
        <li><a href="#with-docker">With Docker</a></li>
        <li><a href="#with-docker">With RabbitMQ</a></li>
      </ul>
    </li>
    <li><a href="#add-info">Additional Information</a></li>
    <li><a href="#license">License</a></li>
  </ol>
</details>

## About The Project

Game Store is a simple example of a REST API built with PHP Laravel, designed to sell games online via key-game and running on Docker. This project is designed as a Microservice from a Monolithic Architecture [WebApi_Clean_Architecture](https://github.com/asnhi/WebApi_Clean_Architecture.git), uses RabbitMQ engine to communicate between 2 services and only builds a demo with an add to cart action from the user (as buyer).

<p align="center">
<img src="https://github.com/asnhi/WebAPI_Microservice/assets/104200613/24b77042-d1f6-4512-a0d7-b62b67bbeab4" alt="Readme-Docker-Micro" width="70%">
</p>

The project after installation and started using Docker with the following containers:
<p align="center">
<img src="https://github.com/asnhi/WebAPI_Microservice/assets/104200613/48b473ec-7dd8-4f12-a7d2-ef1a66547b5c" alt="Readme-Docker-Container-Micro" width="70%">
</p>

## Getting Started

### Prerequisites

1. Clone the repository

   ```
   git clone https://github.com/asnhi/WebAPI_Microservice.git
   cd WebAPI_Microservice
   ```

2. Open the project folder, check for the `.env` file, and update the database credentials. Create a MySQL database with the name provided inside the `.env` file. Source SQL in `./database`.

3. Install the composer dependencies

   ```
   composer install
   ```

4. Migrate the tables

   ```
   php artisan migrate
   ```

### Wanna try running on Docker

After you install Docker Desktop, following:
`    docker-compose build --compress
   `

## Usage

### With Docker

Run `docker-compose up` to work with the project, use [`http://localhost:8000`](http://localhost:8000) for Main Service and [`http://localhost:8001` ](http://localhost:8001) for Product Service
Remember to run `docker-compose down` when you finish the project to stop the application and related services.

### With RabbitMQ

Make sure you have started Docker
Create and run a RabbitMQ instance using Docker with `port 15672`.
Log in with `localhost:15672` with the username and password defined in the `.env`
When implementing a queue, it is necessary to enter commands for processing:

```
php artisan fire
php artisan queue:work
```

However, it is necessary to register an event listener

```
php artisan rabbitmq:consume
```

## Additional Information

You can adjust the project structure and add your own code. Additionally, you should refer to Laravel's official documentation on Clean Architecture and how to use RabbitMQ for Microservice architecture for more detailed information on their principles and best practices.
## License

This project template is open-source and available under the [the MIT license](https://github.com/vietnam-devs/coolstore-microservices/blob/master/LICENSE). Feel free to use it for your own projects and make any modifications as necessary.
