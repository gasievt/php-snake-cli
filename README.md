# php-snake-cli
![GIF](https://i.ibb.co/XtKQdqG/2024-02-09-23-24-11-online-video-cutter-com.gif)
<br>
### HOW TO RUN:
1. Install ncurses extension
```properties
git clone https://github.com/OOPS-ORG-PHP/mod_ncurses.git
cd mod_ncurses
phpize
./configure
make
make install
```
The `php.ini` will need to be adjusted, and an `extension=ncurses` line will need to be added before the extension can be used.
<br>
<br>
2. Run it
```properties
php snake.php
```
### CONTROLS:
 - Arrow keys to control the snake
 - Q to quit the game
