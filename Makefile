upload:
	cp -R . /home/julien/termux/substrat/

download:
	cp -R /home/julien/termux/substrat/* ./

test:
	./vendor/bin/phpunit tests --display-deprecations
