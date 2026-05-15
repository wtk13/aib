.PHONY: up down fresh logs shell

up:
	docker compose up -d --wait

down:
	docker compose down

fresh:
	docker compose down -v
	docker compose up -d --wait
	bin/art migrate:fresh --seed

logs:
	docker compose logs -f

shell:
	docker compose exec app sh
