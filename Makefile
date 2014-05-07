.PHONY: all build test clean install uninstall

all: build

build:
	@mkdir -p ./dist;./makelos release:generate --format=tar --path=./dist;
	@echo "PhpOnRails built. Run \"sudo make install\" to install it."

install:
	@mkdir -p /usr/local/lib/phponrails
	@git archive master | tar -x -C /usr/local/lib/phponrails;
	@echo 'phponrails installed at /usr/local/lib/phponrails.'
	@ln -s /usr/local/lib/phponrails/phponrails /usr/local/bin/phponrails
	@ln -s /usr/local/lib/phponrails/mrails /usr/local/bin/mrails
	@echo 'Linking phponrails and mrails in /usr/local/bin/.'
	@echo 'Done.'

uninstall:
	@echo 'Removing /usr/local/lib/phponrails, /usr/local/bin/phponrails, /usr/local/bin/mrails.'
	@rm -rf /usr/local/lib/phponrails
	@rm /usr/local/bin/phponrails
	@rm /usr/local/bin/mrails
	@echo 'Done.'

clean:
	@rm -rf dist

test:
	@./makelos test:units