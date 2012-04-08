<?php
namespace Utopia\Components\DataParser;

interface IParserEngine {
    public function getType();

    /**
     * input can be file or data
     * @param $input
     */
    public function acceptExtract($input);

    /**
     * data is persumably to be array
     * @param $data
     */
    public function acceptPack($data);

    public function extract($input, $args=array());
    public function pack($data);
}