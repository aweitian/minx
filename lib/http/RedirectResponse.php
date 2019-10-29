<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aw\Http;

/**
 * Response represents an HTTP response.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RedirectResponse extends Response
{
    protected $url;
    /**
     * Constructor.
     *
     * @param string $url
     * @param int $status The response status code
     * @param array $headers An array of response headers
     *
     */
    public function __construct($url = '', $status = 302, $headers = array())
    {
        $this->headers = $headers;
        $this->url = $url;
        parent::__construct('',$status,$headers);
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return $this
     */
    public function send()
    {
        header("location:".$this->url);
        return $this;
    }
}
