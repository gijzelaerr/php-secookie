<?php

/**
 * Copyright (c) 2017 François Kooman <fkooman@tuxed.net>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace fkooman\Cookie;

use DateInterval;
use DateTime;
use fkooman\Cookie\Exception\SessionException;

class Session extends Cookie
{
    /** @var array */
    private $sessionOptions;

    /**
     * @param array $sessionOptions
     */
    public function __construct(array $sessionOptions = [], HeaderInterface $header = null)
    {
        $this->sessionOptions = array_merge(
            [
                // this includes the default settings from Cookie...
                'DomainBinding' => null,       // also bind session to Domain
                'PathBinding' => null,         // also bind session to Path
            ],
            $sessionOptions
        );

        if (is_null($header)) {
            $header = new PhpHeader();
        }
        parent::__construct($sessionOptions, $header);

        if (PHP_SESSION_ACTIVE !== session_status()) {
            session_start();
        }

        $this->sessionCanary();
        $this->domainBinding();
        $this->pathBinding();

        $this->replace(session_name(), session_id());
    }

    public function id()
    {
        return session_id();
    }

    public function regenerate($deleteOldSession = false)
    {
        session_regenerate_id($deleteOldSession);
        $this->replace(session_name(), session_id());
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function delete($key)
    {
        if (!$this->has($key)) {
            throw new SessionException(sprintf('key "%s" not available in session', $key));
        }

        unset($_SESSION[$key]);
    }

    public function has($key)
    {
        return array_key_exists($key, $_SESSION);
    }

    public function get($key)
    {
        if (!$this->has($key)) {
            throw new SessionException(sprintf('key "%s" not available in session', $key));
        }

        return $_SESSION[$key];
    }

    public function destroy()
    {
        $_SESSION = [];
        $this->regenerate(true);
    }

    private function sessionCanary()
    {
        $dateTime = new DateTime();
        if (!array_key_exists('Canary', $_SESSION)) {
            $_SESSION = [];
            $this->regenerate(true);
            $_SESSION['Canary'] = $dateTime->format('Y-m-d H:i:s');
        } else {
            $canaryDateTime = new DateTime($_SESSION['Canary']);
            $canaryDateTime->add(new DateInterval('PT01H'));
            if ($canaryDateTime < $dateTime) {
                $this->regenerate(true);
                $_SESSION['Canary'] = $dateTime->format('Y-m-d H:i:s');
            }
        }
    }

    private function domainBinding()
    {
        $this->sessionBinding('DomainBinding');
    }

    private function pathBinding()
    {
        $this->sessionBinding('PathBinding');
    }

    private function sessionBinding($key)
    {
        if (!is_null($this->sessionOptions[$key])) {
            if (!array_key_exists($key, $_SESSION)) {
                $_SESSION[$key] = $this->sessionOptions[$key];
            }
            if ($this->sessionOptions[$key] !== $_SESSION[$key]) {
                throw new SessionException(sprintf('session bound to %s, we got "%s", but expected "%s"', $key, $_SESSION[$key], $this->sessionOptions[$key]));
            }
        }
    }
}
