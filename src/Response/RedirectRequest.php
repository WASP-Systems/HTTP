<?php
/*
This is part of Wedeto, the WEb DEvelopment TOolkit.
It is published under the MIT Open Source License.

Copyright 2017, Egbert van der Wal

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Wedeto\HTTP\Response;

use Wedeto\HTTP\URL;
use Wedeto\Util\Functions as WF;

/**
 * Provides a way to generate interceptable and testable redirects
 */
class RedirectRequest extends Response
{
    private $url;
    private $timeout;

    /**
     * Create a new Redirect request.
     * @param URL $url The URL where to redirect to
     * @param int $status_code A suggestion for the HTTP status code. By
     *                         default 307: temporary redirect.
     * @param int $timeout The amount of seconds to wait before performing the redirect
     * @param Throwable $previous A chained exception. Can be null
     */
    public function __construct($url, int $status_code = 303, int $timeout = 0, $previous = null)
    {
        // 3XX are only valid redirect status codes
        if ($status_code < 300 || $status_code > 399)
            throw new \InvalidArgumentException("A redirect should have a 3XX status code, not: " . $status_code);
        
        if (is_string($url))
            $url = new URL($url);

        if (!($url instanceof URL))
            throw new \TypeError("Invalid URL: " . WF::str($url));

        parent::__construct("Redirect request to: " . $url, $status_code, $previous);
        $this->url = $url;
        $this->timeout = $timeout;
    }

    /**
     * Set the URL to redirect to
     * @param URL $url The target URL
     * @return Wedeto\HTTP\Response\RedirectRequest Provides fluent interface
     */
    public function setURL(URL $url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Return the URL to where the redirect points
     * @return URL The URL
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Return the redirection headers
     */
    public function getHeaders()
    {
        $h = array();
        if (!empty($this->timeout))
            $h['Refresh'] = $this->timeout . '; url=' . $this->url;
        else
            $h['Location'] = (string)$this->url;
        return $h;
    }

    /**
     * The redirect itself produces no output, but if a previous / chained
     * Response is available, output may be generated.
     */
    public function output(string $mime)
    {
        $prev = $this->getPrevious();
        if ($prev instanceof Response)
            $prev->output($mime);
    }
}
