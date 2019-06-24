<?php

namespace QuickBooksOnline\Payments\Interceptors;

use QuickBooksOnline\Payments\HttpClients\Request\{RequestInterface};
use QuickBooksOnline\Payments\HttpClients\Response\{ResponseInterface};
use QuickBooksOnline\Payments\PaymentClient;

class RequestResponseLoggerInterceptor implements InterceptorInterface
{

  /**
   * The directory to write file
   */
    private $logFolderLocation;
    private $timeZone;

    public function __construct(string $directory, string $timezone)
    {
        $this->setLogDirectory($directory);
        $this->setTimeZone($timezone);
    }

    public function before(RequestInterface &$request, PaymentClient $client) : void
    {
    }
    public function after(ResponseInterface &$response, PaymentClient $client): void
    {
    }

    public function intercept(RequestInterface $request, ResponseInterface $response, PaymentClient $client) : void
    {
        $this->logRequest($request);
        $this->logResponse($response);
    }

    private function logRequest(RequestInterface $request)
    {
        $fileName = $this->generateUniqueFileName($request);
        $filepath = $this->getFilePath($fileName);
        $input = $this->writeType($request)
              . $this->writeUrl($request)
              . $this->writeHeader($request)
              . $this->writeBody($request);
        $this->writeToFile($filepath, $input);
    }

    private function writeToFile(string $fileName, string $content)
    {
        try {
            $fp = fopen($fileName, 'w');
            fwrite($fp, $content);
            fclose($fp);
        } catch (\Exception $e) {
            throw new \RuntimeException("Could not open the file:" . $fileName . " to write content:");
        }
    }

    private function getFilePath(string $fileName)
    {
        if (substr($fileName, -1) == '/') {
            return $this->logFolderLocation . $fileName;
        } else {
            return $this->logFolderLocation . "/" . $fileName;
        }
    }

    private function logResponse(ResponseInterface $response)
    {
        $fileName = $this->generateUniqueFileName($response);
        $filepath = $this->getFilePath($fileName);
        $input = $this->writeType($response)
              . $this->writeUrl($response)
              . $this->writeHeader($response)
              . $this->writeBody($response);
        $this->writeToFile($filepath, $input);
    }

    private function generateUniqueFileName($requestOrResponse)
    {
        $type = "Request";
        if ($requestOrResponse instanceof ResponseInterface) {
            $type = "Response";
        }
        return $type . "_" . $this->formatCurrentTime() . "_" . "U" . uniqid() . ".txt";
    }

    private function formatCurrentTime()
    {
        $date = $this->getCurrentTime();
        $needle = array('-', ' ', ':');
        $updatedDate = str_replace($needle, "_", $date);
        return $updatedDate;
    }

    private function getCurrentTime()
    {
        $now = new \DateTime('now', new \DateTimeZone($this->timeZone));
        return $now->format('Y-m-d H:i:s');
    }


    private function writeType($requestOrResponse)
    {
        $type = "Request";
        if ($requestOrResponse instanceof ResponseInterface) {
            $type = "Response";
        }
        return $type . " at [" . $this->getCurrentTime() . "]" . $this->sectionDivider();
    }

    private function writeUrl($requestOrResponse)
    {
        $request = $requestOrResponse;
        if ($requestOrResponse instanceof ResponseInterface) {
            $request = $requestOrResponse->getAssociatedRequest();
        }
        return $request->getMethod() . " " . $request->getUrl() . $this->sectionDivider();
    }

    private function writeHeader($requestOrResponse)
    {
        $headers = $requestOrResponse->getHeader();
        $collapsedHeaders = array();
        foreach ($headers as $key=>$val) {
            if (!strcmp($key, "Authorization") == 0) {
                $collapsedHeaders[] = "{$key}: {$val}";
            }
        }
        return implode("\n", $collapsedHeaders) . $this->sectionDivider();
    }

    private function writeBody($requestOrResponse)
    {
        $body = $requestOrResponse->getBody();
        $json_string = $this->prettyPrint($body);
        return  $json_string . $this->sectionDivider();
    }


    private function prettyPrint(string $json) : string
    {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = null;
        $json_length = strlen($json);

        for ($i = 0; $i < $json_length; $i++) {
            $char = $json[$i];
            $new_line_level = null;
            $post = "";
            if ($ends_line_level !== null) {
                $new_line_level = $ends_line_level;
                $ends_line_level = null;
            }
            if ($in_escape) {
                $in_escape = false;
            } elseif ($char === '"') {
                $in_quotes = !$in_quotes;
            } elseif (! $in_quotes) {
                switch ($char) {
                  case '}': case ']':
                      $level--;
                      $ends_line_level = null;
                      $new_line_level = $level;
                      break;

                  case '{': case '[':
                      $level++;
                      // no break
                  case ',':
                      $ends_line_level = $level;
                      break;

                  case ':':
                      $post = " ";
                      break;

                  case " ": case "\t": case "\n": case "\r":
                      $char = "";
                      $ends_line_level = $new_line_level;
                      $new_line_level = null;
                      break;
              }
            } elseif ($char === '\\') {
                $in_escape = true;
            }
            if ($new_line_level !== null) {
                $result .= "\n".str_repeat("\t", $new_line_level);
            }
            $result .= $char.$post;
        }
        return $result;
    }

    private function sectionDivider()
    {
        return "\n\n==================================\n\n";
    }

    public function setLogDirectory(string $directory)
    {
        $this->checkIsDirectoryWritable($directory);
        $this->logFolderLocation = $directory;
        return $this;
    }

    public function getLogDirectory() : string
    {
        return $this->logFolderLocation;
    }

    private function checkIsDirectoryWritable(string $directory)
    {
        if (!is_dir($directory) || !is_writable($directory)) {
            throw new \RuntimeException("$directory is either not a valid directory or is not writable.");
        }
    }

    public function setTimeZone(string $timeZone)
    {
        $this->timeZone = $timeZone;
        return $this;
    }

    public function config(array $configuration) : void
    {
    }
}
