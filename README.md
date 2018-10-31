# Adjacency List model for Laravel 4

[![Build Status](https://travis-ci.org/goez-tools/tree-data.svg?branch=master)](https://travis-ci.org/goez-tools/tree-data) [![Code Climate](https://codeclimate.com/github/jaceju/goez-tree-data/badges/gpa.svg)](https://codeclimate.com/github/jaceju/goez-tree-data) [![Test Coverage](https://codeclimate.com/github/jaceju/goez-tree-data/badges/coverage.svg)](https://codeclimate.com/github/jaceju/goez-tree-data)

Goez/TreeData is an adjacency list visitor for eloquant model.

## Requirement

PHP 5.4+

## Usage

Goez/TreeData is designed for Laravel 4.2+, just follow the steps below:

1. Install from composer.

    ```bash
    composer require goez/tree-data
    ```

2. Add `tree` method in your eloquant model:

    ```php
    use Goez\TreeData\Tree;
    use Illuminate\Database\Eloquent\Model as Eloquent;

    class Menu extends Eloquent
    {
        /**
         * @return \Goez\TreeData\Visitor\Eloquent
         */
        public function tree()
        {
            return Tree::accept($this);
        }
    }
    ```

## Examples

Find examples in `tests` folder.

## License

MIT
