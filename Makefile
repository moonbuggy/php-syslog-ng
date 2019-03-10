#!/bin/sh 

container-name := php-syslog-ng
container-image = moonbuggy/$(container-name)

.PHONY: build build-context start clean mrproper

build: Dockerfile
	docker build --rm --build-arg BUILD_DATE=$(shell date -u +'%Y-%m-%dT%H:%M:%SZ') -t $(container-image) .

build-context: Dockerfile.build-context
	docker build -f Dockerfile.build-context -t build-context .
	docker run --rm -it build-context

start: build 
	docker create --name $(container-name) -v php-syslog-ng_conf:/var/www/app/config/ $(container-image)
	docker start $(container-name)

clean:
	docker stop $(container-name)
	docker rm $(container-name)

mrproper: clean
	docker rmi $(container-image)


