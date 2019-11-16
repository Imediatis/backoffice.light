@echo off
php ..\vendor\bin\doctrine orm:schema-tool:update --dump-sql -f
@echo on
