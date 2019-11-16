@echo off
php ..\vendor\bin\doctrine orm:schema-tool:update --dump-sql
@echo on
