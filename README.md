# The Guardian feeds

## Prerequisites
1. Docker must be installed
2. Port 8000 should be free

## Setup steps
1. Run the following command to Builds, create, start, and attaches to containers:
```bash
    docker-compose up -d
```
2. The application will be served at http:://localhost:8000
3. Visit the route feeds/{section} to access the feeds. example: [http://localhost:8000/feeds/football](http://localhost:8000/feeds/football)
