<?php
$ncurse = ncurses_init();
ncurses_curs_set(0);
$board = new Board();
$snake = new Snake($board->getBoardYX()['y'], $board->getBoardYX()['x']);
$snake->draw($board->board);
$food = new Food($board);
ncurses_timeout(0);

while(true){
    $pressed = ncurses_getch();
        switch ($pressed) {
            case NCURSES_KEY_UP:
                if($snake->direction!=='south'){
                    $snake->changeDirection('north');
                    break;
                }
                break;
            case NCURSES_KEY_DOWN:
                if($snake->direction!=='north'){
                    $snake->changeDirection('south');
                    break;
                }
                break;
            case NCURSES_KEY_LEFT:
                if($snake->direction!=='east'){
                    $snake->changeDirection('west');
                    break;   
                }
                break; 
            case NCURSES_KEY_RIGHT:
                if($snake->direction!=='west'){
                    $snake->changeDirection('east');
                    break;
                }
                break;
            case ord("q"):
                ncurses_end();
                die(); 
        }
    $snake->update($board, $food);
    $snake->draw($board->board);
    $food->draw();

    usleep(100000);
}

class Board {
    public $board;
    protected $y;
    protected $x;
    public function __construct(){
        $fullscreen = ncurses_newwin(0, 0, 0, 0);
        ncurses_border(0,0, 0,0, 0,0, 0,0);
        ncurses_getmaxyx($fullscreen, $this->y, $this->x);
        $this->board = ncurses_newwin(intdiv($this->y,2), intdiv($this->x,2), intdiv($this->y,2)-intdiv($this->y,4), intdiv($this->x,2)-intdiv($this->x,4));
        ncurses_wborder($this->board, ord('|'), ord('|'), 0, 0, 0, 0, 0, 0);
        ncurses_refresh();
    }
    public function getBoardYX(){
        $y; $x;
        ncurses_getmaxyx($this->board, $y, $x);
        return ['y'=>$y, 'x'=>$x];
    }
}

class Food{
    public array $location;
    protected $boardW;
    public function __construct(Board $board){
        $this->boardW = $board->board;
        $boardYX = $board->getBoardYX();
        $this->location = ['y'=>rand(1, $boardYX['y']-2), 'x'=>rand(1, $boardYX['x']-2)];
        $this->draw();
    }
    public function respawnFood(Board $board){
        $this->boardW = $board->board;
        $boardYX = $board->getBoardYX();
        $this->location = ['y'=>rand(1, $boardYX['y']-2), 'x'=>rand(1, $boardYX['x']-2)];
        $this->draw();
    }
    public function draw(){
        ncurses_mvwaddstr($this->boardW, $this->location['y'], $this->location['x'], '$');
        ncurses_wrefresh($this->boardW);
    }
}

class Snake{
    protected int $y;
    protected int $x;
    protected array $body;
    public string $direction = 'east';
    public function __construct(int $y, int $x){
        $this->y = $y;
        $this->x = $x;
        $this->body = [['x' => (intdiv($this->x,2)), 'y'=>intdiv($this->y,2)]];
    }

    public function isGameOver(Food $food){
        if($this->body[0]['x']===0 || $this->body[0]['y']===0 ||
        $this->body[0]['x']===$this->x-1 || $this->body[0]['y']===$this->y-1 || $this->isBiteItself()){
            return true;
        }
    }

    public function isBiteItself(){
        $body = $this->body;
        $head = array_shift($body);
        foreach($body as $el){
            if($head['x']===$el['x'] && $head['y']===$el['y']){
                return true;
            }
        }
    }

    public function changeDirection($newDirection){
        if (in_array($newDirection, ['east', 'west', 'north', 'south'])) {
            $this->direction = $newDirection;
            $head = $this->body[0];
            $tail = array_pop($this->body);
            $tail=$head;
            array_unshift($this->body, $tail);
        }
        
     }
     
    public function draw($board){
        foreach($this->body as $segment){
            ncurses_mvwaddstr($board, $segment['y'], $segment['x'], 'X');
            ncurses_wrefresh($board);
        }
    }

    public function update($board, $food){
        $head = $this->body[0];
        $tail = array_pop($this->body);
        $tail = $head;
        ncurses_werase($board->board);
        ncurses_wborder($board->board, ord('|'), ord('|'), 0, 0, 0, 0, 0, 0);
        ncurses_wrefresh($board->board);
        switch($this->direction){
            case 'east':
                $tail['x']++;
                array_unshift($this->body, $tail);
                break;
            case 'west':
                $tail['x']--;
                array_unshift($this->body, $tail);
                break;
            case 'north':
                $tail['y']--;
                array_unshift($this->body,$tail);
                break;
            case 'south':
                $tail['y']++;
                array_unshift($this->body, $tail);
        }
        $this->eat($food, $board);
        $this->draw($board->board);
        if($this->isGameOver($food)){
            ncurses_end();
            die();
        }
    }
    
    public function eat(Food $food, Board $board){
        if($food->location['y']===$this->body[0]['y'] && $food->location['x']===$this->body[0]['x']){
            switch($this->direction){
                case 'east':
                    array_push($this->body, ['y'=>$this->body[0]['y'], 'x'=>$this->body[0]['x']+1]);
                    break;
                case 'west':
                    array_push($this->body, ['y'=>$this->body[0]['y'], 'x'=>$this->body[0]['x']-1]);
                    break;
                case 'north':
                    array_push($this->body, ['y'=>$this->body[0]['y']-1, 'x'=>$this->body[0]['x']]);
                    break;
                case 'south':
                    array_push($this->body, ['y'=>$this->body[0]['y']+1, 'x'=>$this->body[0]['x']]);
                    break;
            }
            $food->respawnFood($board);
        }
    }

    public function getSnakeCoords(){
        return $this->body;
    }
}