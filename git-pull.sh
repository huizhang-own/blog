#!/bin/bash
while ((1))
do
	sleep 30;
	git add .;
	git commit -m 'up';
	git pull;
done