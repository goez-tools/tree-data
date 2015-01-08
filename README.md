# Adjacency List model for Laravel 4

[![Build Status](https://travis-ci.org/jaceju/goez-tree-data.svg)](https://travis-ci.org/jaceju/goez-tree-data) [![Code Climate](https://codeclimate.com/github/jaceju/goez-tree-data/badges/gpa.svg)](https://codeclimate.com/github/jaceju/goez-tree-data) [![Test Coverage](https://codeclimate.com/github/jaceju/goez-tree-data/badges/coverage.svg)](https://codeclimate.com/github/jaceju/goez-tree-data)

Goez/TreeData is an adjacency list visitor for eloquant model.

## Requirement

PHP 5.4+

## Installation

you can use it in any PHP project with composer:

```bash
composer require goez/tree-data
```

### Laravel 4

Goez/TreeData was designed for Laravel 4.2+, just follow the steps below:

1. Install from composer.

2. Add `tree` method in your eloquant model:

    ```php
    use Goez\TreeData\Tree;
    use Illuminate\Database\Eloquent\Model as Eloquent;
    use Illuminate\Database\Schema\Blueprint as Table;

    class Menu extends Eloquent
    {
        protected $table = 'nodes';
        public $timestamps = false;
        protected $guarded = array();

        /**
         * @return \Goez\TreeData\Visitor\Eloquent
         */
        public function tree()
        {
            return Tree::accept($this);
        }
    }
    ```

## Example

Find examples in `tests` folder.

## License

MIT
