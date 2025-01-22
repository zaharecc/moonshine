.PHONY: assets

assets:
	npm run build
	cd .. && php artisan vendor:publish --tag=moonshine-assets --force
