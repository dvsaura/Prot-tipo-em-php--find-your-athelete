@echo off

cd /d "%~dp0"

echo Parando tudo

call parar.bat
call remover.bat 

echo Processo concluido
