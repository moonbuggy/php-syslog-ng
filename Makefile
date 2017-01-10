
container-name := alpine-nginx-php5
container-image = glebpoljakov/$(container-name)

.PHONY: build start clean mrproper

build: Dockerfile
	docker build --rm -t $(container-image) .

start: build 
	docker create --name alpine-nginx-php5 -p 8880:80 $(container-image)
	docker start alpine-nginx-php5

clean:
	docker stop $(container-name)
	docker rm $(container-name)

mrproper: clean
	docker rmi $(container-image)


