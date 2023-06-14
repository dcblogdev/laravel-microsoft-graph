<?php

namespace Dcblogdev\MsGraph\Helpers;

class Paginator
{
    protected $perPage;
    protected $instance;
    protected $page;
    protected $totalRows = 0;

    public function __construct($perPage, $instance)
    {
        $this->instance = $instance;
        $this->perPage  = $perPage;
        $this->setInstance();
    }

    public function get_start()
    {
        return ($this->page * $this->perPage) - $this->perPage;
    }

    protected function setInstance()
    {
        $this->page = (int) (!isset($_GET[$this->instance]) ? 1 : $_GET[$this->instance]);
        $this->page = ($this->page == 0 ? 1 : ($this->page < 0 ? 1 : $this->page));
    }

    public function setTotal($totalRows)
    {
        $this->totalRows = $totalRows;
    }

    public function page_links()
    {
        $path = '?';
        $queryParams = request()->collect()->except($this->instance);
        if (count($queryParams->all()) !== 0) {
            $path = $path.http_build_query($queryParams->all()).'&';
        }

        $adjacents = "2";
        $prev      = $this->page - 1;
        $next      = $this->page + 1;
        $lastpage  = ceil($this->totalRows / $this->perPage);
        $lpm1      = $lastpage - 1;

        $pagination = "";
        if ($lastpage > 1) {
            $pagination .= "<ul class='pagination'>";
            if ($this->page > 1) {
                $pagination .= "<li><a href='".$path."$this->instance=$prev'>Previous</a></li>";
            } else {
                $pagination .= "<li><span class='disabled'>Previous</span></li>";
            }

            if ($lastpage < 7 + ($adjacents * 2)) {
                for ($counter = 1; $counter <= $lastpage; $counter++) {
                    if ($counter == $this->page) {
                        $pagination .= "<li><span class='current'>$counter</span></li>";
                    } else {
                        $pagination .= "<li><a href='".$path."$this->instance=$counter'>$counter</a></li>";
                    }
                }
            } elseif ($lastpage > 5 + ($adjacents * 2)) {
                if ($this->page < 1 + ($adjacents * 2)) {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                        if ($counter == $this->page) {
                            $pagination .= "<li><span class='current'>$counter</span></li>";
                        } else {
                            $pagination .= "<li><a href='".$path."$this->instance=$counter'>$counter</a></li>";
                        }
                    }
                    $pagination .= "...";
                    $pagination .= "<li><a href='".$path."$this->instance=$lpm1'>$lpm1</a></li>";
                    $pagination .= "<li><a href='".$path."$this->instance=$lastpage'>$lastpage</a></li>";
                } elseif ($lastpage - ($adjacents * 2) > $this->page && $this->page > ($adjacents * 2)) {
                    $pagination .= "<li><a href='".$path."$this->instance=1'>1</a></li>";
                    $pagination .= "<li><a href='".$path."$this->instance=2'>2</a></li>";
                    $pagination .= "...";
                    for ($counter = $this->page - $adjacents; $counter <= $this->page + $adjacents; $counter++) {
                        if ($counter == $this->page) {
                            $pagination .= "<li><span class='current'>$counter</span></li>";
                        } else {
                            $pagination .= "<li><a href='".$path."$this->instance=$counter'>$counter</a></li>";
                        }
                    }
                    $pagination .= "..";
                    $pagination .= "<li><a href='".$path."$this->instance=$lpm1'>$lpm1</a></li>";
                    $pagination .= "<li><a href='".$path."$this->instance=$lastpage'>$lastpage</a></li>";
                } else {
                    $pagination .= "<li><a href='".$path."$this->instance=1'>1</a></li>";
                    $pagination .= "<li><a href='".$path."$this->instance=2'>2</a></li>";
                    $pagination .= "..";
                    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                        if ($counter == $this->page) {
                            $pagination .= "<li><span class='current'>$counter</span></li>";
                        } else {
                            $pagination .= "<li><a href='".$path."$this->instance=$counter'>$counter</a></li>";
                        }
                    }
                }
            }

            if ($this->page < $counter - 1) {
                $pagination .= "<li><a href='".$path."$this->instance=$next'>Next</a></li>";
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