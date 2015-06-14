<?php
/**
 * Copyright 2015 Yoel Nunez <dev@nunez.guru>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

namespace JuiceKit\Plug;


use JuiceKit\Http\Request;
use JuiceKit\Http\Response;
use JuiceKit\Plug\Exception\InvalidFilterException;
use JuiceKit\Plug\Exception\MissingFilterException;
use JuiceKit\Plug\Filter\FilterInterface;

class HttpInterceptor
{
    private $mapping = array();

    function __construct($mapping)
    {
        $this->mapping = $mapping;
    }


    public function filter()
    {
        $uri = $_SERVER['REQUEST_URI'];

        $plugs = array();

        $request = new Request();
        $response = new Response();

        foreach ($this->mapping['filter_map'] as $filter => $paths) {
            if (!isset($this->mapping['filter'][$filter])) {
                throw new MissingFilterException(sprintf("%s is missing a filter definition", $filter));
            }
            foreach ($paths as $path) {
                if (preg_match('(^' . $path . '$)', $uri)) {
                    $filterName = $this->mapping['filter'][$filter];

                    /** @var $plug FilterInterFace */
                    $plug = null;

                    if (!isset($plugs[$filter])) {
                        $plug = new $filterName();

                        if (!($plug instanceof FilterInterface)) {
                            throw new InvalidFilterException(sprintf("%s is not of type JuiceKit\\Plug\\Filter\\FilterInterface"));
                        }

                        $plugs[$filter] = $plug;
                    } else {
                        $plug = $plugs[$filter];
                    }

                    $plug->handleFilter($request, $response);
                }
            }
        }
    }

} 