@echo off
echo Parando os conteineres do projeto Find Your Athlete...

docker stop fya-php fya-mysql 2>nul

echo Conteineres parados com sucesso!