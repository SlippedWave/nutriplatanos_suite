# Nutriplatanos Suite Makefile for Laravel

# Variables
PHP = php
COMPOSER = composer
NPM = npm
ARTISAN = $(PHP) artisan

# Default target
.PHONY: all
all: build

# Build the project
.PHONY: build
build: install
	@echo "Building the project..."
	$(NPM) run build

# Clean build artifacts
.PHONY: clean
clean:s
	@echo "Cleaning build artifacts..."
	rm -rf public/build
	rm -rf node_modules
	rm -rf vendor
	rm -rf .phpunit.result.cache

# Run tests
.PHONY: test
test:
	@echo "Running tests..."
	$(PHP) artisan test

# Install dependencies
.PHONY: install
install:
	@echo "Installing dependencies..."
	$(COMPOSER) install
	$(NPM) install

# Run the application
.PHONY: run
run:
	@echo "Running the application..."
	@echo "Starting the Laravel server..."
	$(ARTISAN) serve

# Run the npm run dev command
.PHONY: npm-dev
npm-dev:
	@echo "Running npm in development mode..."
	$(NPM) run dev

# Database migrations
.PHONY: migrate
migrate:
	@echo "Running database migrations..."
	$(ARTISAN) migrate

# Database seeding
.PHONY: seed
seed:
	@echo "Seeding the database..."
	$(ARTISAN) db:seed

# Help
.PHONY: help
help:
	@echo "Available commands:"
	@echo "  make all      - Build the project (default)"
	@echo "  make build    - Build the project"
	@echo "  make clean    - Remove build artifacts"
	@echo "  make test     - Run tests"
	@echo "  make install  - Install dependencies"
	@echo "  make run      - Run the application"
	@echo "  make npm-dev  - Run npm in development mode"
	@echo "  make migrate  - Run database migrations"
	@echo "  make seed     - Seed the database"
	@echo "  make help     - Display this help message"