<?php
/**
 * @name Core_Paging
 * @desc 分页类
 * @date   2016-10-11
 */
class Core_Paging
{

    const PAGE_SIZE = 10;
    const PAGE = 1;

    /**
     * 获取分页参数
     *
     * @param $page
     * @param $pageSize
     * @param $asString
     *
     * @return array
     */
    public static function getLimit($page, $pageSize, $asString = false)
    {
        $limit = array(
            'offset'   => 0,
            'pageSize' => 10,
        );
        if(!is_integer($page) || !is_integer($pageSize))
        {
            return $limit;
        }

        if($page > 1)
        {
            $limit['offset'] = ($page - 1) * $pageSize;
        }
        $limit['pageSize'] = $pageSize;
        if($asString)
        {
            return ' limit ' . $limit['offset'] . ',' . $limit['pageSize'] . ' ';
        }

        return $limit;
    }




    /**
     * 统一输出分页参数
     *
     * @param $page       int 当前页码
     * @param $pageSize   int 每页数量
     * @param $totalCount int 总条数
     *
     * @return array
     */
    public static function formatPaging($page, $pageSize, $totalCount)
    {
        $paging = array(
            'page'      => $page,
            'pageSize'  => $pageSize,
            'total'     => $totalCount,
            'totalPage' => $pageSize > 0 ? ceil($totalCount / $pageSize) : 1,
        );

        return $paging;
    }

    /**
     * 获取分页数据
     *
     * @param       $url
     * @param       $page
     * @param       $totalCount
     * @param int   $pageSize
     * @param array $params
     *
     * @return mixed
     */
    public static function getPagingData($url, $page, $totalCount, $pageSize = self::PAGE_SIZE, $params = array())
    {
        $data['display']  = false;
        $data['previous'] = '';
        $data['next']     = '';

        $totalPage = ceil($totalCount / $pageSize);
        if($totalPage > 1)
        {
            $data['display'] = true;
            if($page == 1)
            {
                $data['next'] = $url . "?pageSize={$pageSize}&page=" . ($page + 1) . self::buildQuery($params);
            }
            else if($page == $totalPage)
            {
                $data['previous'] = $url . "?pageSize={$pageSize}&page=" . ($page - 1) . self::buildQuery($params);
            }
            else
            {
                $data['previous'] = $url . "?pageSize={$pageSize}&page=" . ($page - 1) . self::buildQuery($params);
                $data['next']     = $url . "?pageSize={$pageSize}&page=" . ($page + 1) . self::buildQuery($params);
            }
        }

        return $data;
    }

    /**
     * @param array $params
     * @return string
     */
    private static function buildQuery(array $params)
    {
        $query = '';
        foreach($params as $key => $value)
        {
            $query .= '&' . $key . '=' . $value;
        }

        return $query;
    }
}