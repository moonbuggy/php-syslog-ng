
container-name := alpine-nginx-php5
container-image = gleb.poljakov/$(container-name)

.PHONY: build start cleanup

build: Dockerfile
	docker build --rm -t $(container-image) .

start: build 
	docker create --name alpine-nginx-php5 -p 8880:80 $(container-image)
	docker start alpine-nginx-php5

cleanup:
	docker stop $(container-name)
	docker rm $(container-name)
	docker rmi $(container-image)

