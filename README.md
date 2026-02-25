News aggregator with AI-powered short summary generation.

## Instalation
1. cp .env.example .env
2. Edit DB section, RABBITMQ section, REDIS section, MAIL section, GOOGLE section, and API keys
3. docker compose up -d --build
4. docker compose exec php composer install --no-dev --optimize-autoloader
5. docker compose exec php php artisan key:generate
6. docker compose exec php php artisan migrate --force
7. docker compose exec php php artisan optimize
9. docker compose exec php npm i
10. docker compose exec php npm run build