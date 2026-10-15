@echo off
echo Removendo conteineres, imagens e rede...

docker rm -f fya-php fya-mysql 2>nul
docker rmi fya-image fya-mysql-image 2>nul
docker network rm rede-fya 2>nul

echo Limpeza concluida com sucesso!