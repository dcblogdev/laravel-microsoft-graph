<?php

namespace Dcblogdev\MsGraph;

/*
 * PHP Pagination Class
 *
 * @author David Carr - dave@daveismyname.com - http://www.daveismyname.com
 * @version 1.0
 * @date October 20, 2012
 */

class Paginator
{
    /**
     * set the number of items per page.
     *
     * @var numeric
     */
    private $perPage;

    /**
     * set get parameter for fetching the page number
     *
     * @var string
     */
    private $instance;

    /**
     * sets the page number.
     *
     * @var numeric
     */
    private $page;

    /**
     * set the limit for the data source
     *
     * @var string
     */
    private $limit;

    /**
     * set the total number of records/items.
     *
     * @var numeric
     */
    private $totalRows = 0;

    /**
     * set custom css classes for additional flexibility
     *
     * @var sting
     */
    private $customCSS;


    /**
     *  __construct
     *
     *  pass values when class is istantiated
     *
     * @param  numeric  $perPage  sets the number of iteems per page
     * @param  numeric  $instance  sets the instance for the GET parameter
     */
    public function __construct($perPage, $instance, $customCSS = '')
    {
        $this->instance = $instance;
        $this->perPage  = $perPage;
        $this->set_instance();
        $this->customCSS = $customCSS;
    }

    /**
     * get_start
     *
     * creates the starting point for limiting the dataset
     * @return numeric
     */
    public function get_start()
    {
        return ($this->page * $this->perPage) - $this->perPage;
    }

    /**
     * set_instance
     *
     * sets the instance parameter, if numeric value is 0 then set to 1
     *
     * @var numeric
     */
    private function set_instance()
    {
        $this->page = (int) (!isset($_GET[$this->instance]) ? 1 : $_GET[$this->instance]);
        $this->page = ($this->page == 0 ? 1 : ($this->page < 0 ? 1 : $this->page));
    }

    /**
     * set_total
     *
     * collect a numberic value and assigns it to the totalRows
     *
     * @var numeric
     */
    public function set_total($totalRows)
    {
        $this->totalRows = $totalRows;
    }

    /**
     * get_limit
     *
     * returns the limit for the data source, calling the get_start method and passing in the number of items perp page
     *
     * @return string
     */
    public function get_limit()
    {
        return "LIMIT ".$this->get_start().",$this->perPage";
    }

    /**
     * get_limit_keys
     *
     * returns an array of the offset and limit returned on each call
     *
     * @return string
     */
    public function get_limit_keys()
    {
        return ['offset' => $this->get_start(), 'limit' => $this->perPage];
    }

    /**
     * page_links
     *
     * create the html links for navigating through the dataset
     *
     * @return string returns the html menu
     * @var sting $ext optionally pass in extra parameters to the GET
     * @var sting $path optionally set the path for the link
     */
    public function page_links($path = '?', $ext = null)
    {
        $adjacents = "2";
        $prev      = $this->page - 1;
        $next      = $this->page + 1;
        $lastpage  = ceil($this->totalRows / $this->perPage);
        $lpm1      = $lastpage - 1;

        $pagination = "";
        if ($lastpage > 1) {
            $pagination .= "<ul class='pagination ".$this->customCSS."'>";
            if ($this->page > 1) {
                $pagination .= "<li><a href='".$path."$this->instance=$prev"."$ext'>Previous</a></li>";
            } else {
                $pagination .= "<li><span class='disabled'>Previous</span></li>";
            }

            if ($lastpage < 7 + ($adjacents * 2)) {
                for ($counter = 1; $counter <= $lastpage; $counter++) {
                    if ($counter == $this->page) {
                        $pagination .= "<li><span class='current'>$counter</span></li>";
                    } else {
                        $pagination .= "<li><a href='".$path."$this->instance=$counter"."$ext'>$counter</a></li>";
                    }
                }
            } elseif ($lastpage > 5 + ($adjacents * 2)) {
                if ($this->page < 1 + ($adjacents * 2)) {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                        if ($counter == $this->page) {
                            $pagination .= "<li><span class='current'>$counter</span></li>";
                        } else {
                            $pagination .= "<li><a href='".$path."$this->instance=$counter"."$ext'>$counter</a></li>";
                        }
                    }
                    $pagination .= "...";
                    $pagination .= "<li><a href='".$path."$this->instance=$lpm1"."$ext'>$lpm1</a></li>";
                    $pagination .= "<li><a href='".$path."$this->instance=$lastpage"."$ext'>$lastpage</a></li>";
                } elseif ($lastpage - ($adjacents * 2) > $this->page && $this->page > ($adjacents * 2)) {
                    $pagination .= "<li><a href='".$path."$this->instance=1"."$ext'>1</a></li>";
                    $pagination .= "<li><a href='".$path."$this->instance=2"."$ext'>2</a></li>";
                    $pagination .= "...";
                    for ($counter = $this->page - $adjacents; $counter <= $this->page + $adjacents; $counter++) {
                        if ($counter == $this->page) {
                            $pagination .= "<li><span class='current'>$counter</span></li>";
                        } else {
                            $pagination .= "<li><a href='".$path."$this->instance=$counter"."$ext'>$counter</a></li>";
                        }
                    }
                    $pagination .= "..";
                    $pagination .= "<li><a href='".$path."$this->instance=$lpm1"."$ext'>$lpm1</a></li>";
                    $pagination .= "<li><a href='".$path."$this->instance=$lastpage"."$ext'>$lastpage</a></li>";
                } else {
                    $pagination .= "<li><a href='".$path."$this->instance=1"."$ext'>1</a></li>";
                    $pagination .= "<li><a href='".$path."$this->instance=2"."$ext'>2</a></li>";
                    $pagination .= "..";
                    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                        if ($counter == $this->page) {
                            $pagination .= "<li><span class='current'>$counter</span></li>";
                        } else {
                            $pagination .= "<li><a href='".$path."$this->instance=$counter"."$ext'>$counter</a></li>";
                        }
                    }
                }
            }

            if ($this->page < $counter - 1) {
                $pagination .= "<li><a href='".$path."$this->instance=$next"."$ext'>Next</a></li>";
            } else {
                $pagination .= "<li><span class='disabled'>Next</span></li>";
            }
            $pagination .= "</ul>\n";
        }


        return $pagination;
    }

    public function page_links_array($path = '?', $ext = null)
    {
        $lastpage = ceil($this->totalRows / $this->perPage);
        $pages    = [];

        for ($counter = 1; $counter <= $lastpage; $counter++) {
            $pages[] = $counter;
        }

        return $pages;
    }
}