<?php
/**
 * This file automatically generates a test suite for the selenese
 *
 * <code>
 * $generator = new Ilib_Testing_Selenium_Selenese_TestSuiteGenerator;
 * $generator->write();
 * </code>
 */
class Ilib_Testing_Selenium_Selenese_TestSuiteGenerator
{
    private $write;
    private $title;
    private $replacement = array();

    /**
     * Constructor
     *
     * @param string $title Title for the selenese test suite
     *
     * @return void
     */
    function __construct($root = '.', $title = 'Selenium Test Suite')
    {
        $this->title = $title;
        $this->root = $root;
    }

    /**
     * Writes the test suite file
     *
     * @param string $filename Name of the testSuite
     *
     * @throws Exception on error
     * @return boolean
     */
    function write($filename = 'testSuite.html')
    {
        $this->generate();

        @unlink($filename);

        if (!$handle = fopen($filename, 'a')) {
            throw new Exception('Cannot open file ' . $filename);
        }

        // Write $somecontent to our opened file.
        if (fwrite($handle, $this->write) === FALSE) {
            throw new Exception("Cannot write to file ($filename)");
        }

        fclose($handle);

        return true;
    }

    public function generate()
    {
        $this->write  = "<html>" .
            "\n <body>" .
            "\n  <table>" .
            "\n    <tr>" .
            "\n     <td>".$this->title."</td>" .
            "\n    </tr>";

        // The root for search
        $folder = array($this->root);

        // The tmp dir
        $tmp_folder = array_merge($folder, array('.tmp'));
        $this->createFolder($tmp_folder);

        $this->write .= $this->parseFolder($folder, $tmp_folder);
        $this->write .= "\n  </table>" .
          "\n </body>" .
          "\n</html>\n";

        return $this->write;
    }

    private function parseFolder($folder, $tmp_folder)
    {
        $return = '';
        if (count($folder) > 1) {
            $test_name_prepend = implode(':', array_slice($folder, 1));
        } else {
            $test_name_prepend = 'root';
        }

        foreach (scandir(implode(DIRECTORY_SEPARATOR, $folder)) as $item) {
            if (!strcmp(substr($item, 0, 1), '.' )) {
                continue;
            }

            $item_path = implode(DIRECTORY_SEPARATOR, $folder).DIRECTORY_SEPARATOR.$item;

            if (is_dir($item_path)) {
                // it the folder is named generate it is because the tests needs to be
                // generated.
                if ($item == 'generate') {
                    foreach (scandir($item_path) as $generate_file) {
                        if (!strcmp(substr($generate_file, 0, 1), '.' )) {
                            continue;
                        }

                        if (substr($generate_file, strlen($generate_file) - 5) == '.html') {

                            $generate_file_content = file_get_contents($item_path.DIRECTORY_SEPARATOR.$generate_file);
                            foreach ($this->replacement as $token => $replacement) {
                                $generate_file_content = str_replace($token, $replacement, $generate_file_content);
                            }

                            // we create the folder to save the test in tmp_dir
                            // with the current dir as subdirs. array_slice removes
                            // the first item (root dir) from the $folder
                            if(!$tmp_dir = $this->createFolder(array_merge($tmp_folder, array_slice($folder, 1)))) {
                                trigger_error('Unable to create folder: '.implode(DIRECTORY_SEPARATOR, array_merge($tmp_folder, array_shift($folder))), E_USER_ERROR);
                                return false;
                            }

                            file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.$generate_file, $generate_file_content);
                            $test_name = $test_name_prepend.':generated:'.substr($generate_file, 0, strlen($generate_file) - 5);
                            $return .= "\n   <tr>" .
                                  "\n    <td><a href=\"".str_replace('\\', '/', $tmp_dir.'/'.$generate_file)."\">".$test_name."</a></td>" .
                                  "\n   </tr>";
                        }
                    }
                } else {
                    $return .= $this->parseFolder(array_merge($folder, array($item)), $tmp_folder);
                }
            } elseif ($item != 'testSuite.html' && substr($item, strlen($item) - 5) == '.html') {
                $test_name = $test_name_prepend.':'.substr($item, 0, strlen($item) - 5);
                $return .= "\n   <tr>" .
                          "\n     <td><a href=\"".str_replace('\\', '/', $item_path)."\">".$test_name."</a></td>" .
                          "\n   </tr>";

            }
        }
        return $return;
    }

    public function addReplacement($token, $replacement)
    {
        $this->replacement[$token] = $replacement;
    }

    function getReplacements()
    {
        return $this->replacement;
    }

    private function createFolder($folder)
    {
        if (!is_dir($folder[0])) {
            trigger_error('Root folder is not a valid directory', E_USER_ERROR);
            return false;
        }

        $create = $folder[0];
        for ($i = 1, $max = count($folder); $i < $max; $i++) {
            $create .= DIRECTORY_SEPARATOR.$folder[$i];
            if (!is_dir($create)) {
                if (!mkdir($create)) {
                    return false;
                }
            }
        }
        return $create;
    }
}