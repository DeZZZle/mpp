up:
	docker compose -f docker-compose.yml up -d

down:
	docker compose -f docker-compose.yml down

build:
	make down
	docker compose -f docker-compose.yml build

restart:
	make down
	make up
