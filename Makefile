.PHONY: up down fresh logs shell theme

up:
	docker compose up -d --wait

down:
	docker compose down

fresh:
	@echo "WARNING: This will destroy all local database data. Press Ctrl-C to abort, Enter to continue." && read _
	docker compose down -v
	docker compose up -d --wait
	bin/art migrate:fresh --seed

logs:
	docker compose logs -f

shell:
	docker compose exec app sh

theme:
	bin/npm run theme:build
