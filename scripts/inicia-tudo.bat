@echo off

cd /d "%~dp0"


echo  Iniciando automacao do ambiente...


call criar-network.bat
call criar-imagem-mysql.bat
call criar-imagem-php.bat
call criar-container-mysql.bat
call criar-container-php.bat


echo  Processo concluido!
    