<?php

if (!function_exists('cleanUrlQueryParams')) {
    function cleanQueryParams($queryString, $params) {
       parse_str($queryString, $query);
       foreach (explode(',',$params) as $key => $val) {
           if (isset($query[$val])) {
               unset($query[$val]);
           }
       }
       $queryString = http_build_query($query);
       return $queryString;
    }
}
if (!function_exists('setCurrentUrlOrder')) {
    function setCurrentUrlOrder($url, $param, $unsetparams='') {
        $uri = parse_url($url);
        $path= $uri["path"];

        $queryString = (isset($uri["query"])?$uri["query"]:'');

        if (strstr($queryString, 'fieldOrd='.$param)) {
            if (strstr($queryString, 'fieldOrd='.$param.':1')) {
                $queryString = str_replace('fieldOrd='.$param.':1', 'fieldOrd='.$param.':2', $queryString);
            } elseif (strstr($queryString, 'fieldOrd='.$param.':2')) {
                $queryString = str_replace('fieldOrd='.$param.':2', 'fieldOrd='.$param.':1', $queryString);
            } else {
                $queryString = str_replace('fieldOrd='.$param, 'fieldOrd='.$param.':2', $queryString);
            }
            $path = rtrim($path,'/').'/';
            if ($queryString != '') {
                $url = $path.'?'.$queryString;
            }
        } elseif (strstr($queryString, 'fieldOrd=')) {
            $queryString = cleanQueryParams($queryString,'fieldOrd');
            if ($queryString != '') {
                $queryString.='&';
            }
            $queryString.='fieldOrd='.$param.':1';
            $path = rtrim($path,'/').'/';
            if ($queryString != '') {
                $url = $path.'?'.$queryString;
            }
        } else {
            if ($queryString != '') {
                $queryString.='&';
            }
            $queryString.='fieldOrd='.$param.':1';
            $path = rtrim($path,'/').'/';
            if ($queryString != '') {
                $url = $path.'?'.$queryString;
            }
        }

        return $url;
    }
}