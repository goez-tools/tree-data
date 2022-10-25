# Adjacency List model for Laravel Eloquent

![Build Status](https://github.com/goez-tools/tree-data/actions/workflows/php.yml/badge.svg)

Goez/TreeData is an adjacency list visitor for Eloquent model.

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
