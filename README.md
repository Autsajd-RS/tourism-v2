# Documentation

Download **dokumentacija1.pdf** file for better resolution


# Requirements

- docker (20.10.14)
- docker-compose (1.29.2)

# Installation

Clone repository
```bash
git clone <repo>
```

Build docker containers
```bash
docker-compose up -d
```

Enter app container
```bash
docker exec -it tourism-v2_tourism ash
```

Run migrations
```bash
./bin/console doctrine:schema:update --force
```

Populate city table with cities
```bash
./bin/console app:populate-cities
```

Generate jwt auth keypair
```bash
php bin/console lexik:jwt:generate-keypair
```
