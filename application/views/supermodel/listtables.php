        <div id="content_inner">
            <h3>ListTables</h3>
            <hr>
            <form name="find" action="" method="get">
                <div id="content-inner-2">
                    <label>Filter by : </label>
                    <input type="text" name="filter" placeholder="Text" value="<?php echo isset($_GET['filter'])?$_GET['filter']:'';?>" />
                    <button onclick='document.forms[0].submit():'>Search</button>
                    <hr>
                    <?php if ($data->numRows > 0) : ?>
                    <table>
                        <tr>
                            <?php foreach ($data->result[0] as $key => $fields) : ?>
                            <th><a href="<?php echo setCurrentUrlOrder($_SERVER['REQUEST_URI'],$key)?>"><?php echo $key; ?></a></th>
                            <?php endforeach; ?>
                        </tr>
                        <?php foreach ($data->result as $key => $record) : ?>
                            <tr>
                                <?php foreach ($record as $key2 => $val2) : ?>
                                    <td>
                                        <?php echo $val2; ?>
                                    </td>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <tr>
                            <?php echo $data->pagination; ?>
                        </tr>
                    </table>
                    <?php else : ?>
                        NO RECORDS FOUND
                    <?php endif; ?>
            </div>
            </form>
        </div>

        <style>
            .pagination {
                display: inline-block;
                padding-left: 0;
                margin: 20px 0;
                border-radius: 3px;
            }
            .pagination > li {
                display: inline;
            }
            .pagination > li > a,
            .pagination > li > span {
                position: relative;
                float: left;
                padding: 6px 8px;
                line-height: 1.5384616;
                text-decoration: none;
                color: #333333;
                background-color: #fff;
                border: 1px solid #ddd;
                margin-left: -1px;
            }
            .pagination > li:first-child > a,
            .pagination > li:first-child > span {
                margin-left: 0;
                border-bottom-left-radius: 3px;
                border-top-left-radius: 3px;
            }
            .pagination > li:last-child > a,
            .pagination > li:last-child > span {
                border-bottom-right-radius: 3px;
                border-top-right-radius: 3px;
            }
            .pagination > li > a:hover,
            .pagination > li > span:hover,
            .pagination > li > a:focus,
            .pagination > li > span:focus {
                z-index: 2;
                color: #333333;
                background-color: #f5f5f5;
                border-color: #ddd;
            }
            .pagination > .active > a,
            .pagination > .active > span,
            .pagination > .active > a:hover,
            .pagination > .active > span:hover,
            .pagination > .active > a:focus,
            .pagination > .active > span:focus {
                z-index: 3;
                color: #fff;
                background-color: #2196F3;
                border-color: #2196F3;
                cursor: default;
            }
            .pagination > .disabled > span,
            .pagination > .disabled > span:hover,
            .pagination > .disabled > span:focus,
            .pagination > .disabled > a,
            .pagination > .disabled > a:hover,
            .pagination > .disabled > a:focus {
                color: #bbb;
                background-color: transparent;
                border-color: #ddd;
                cursor: not-allowed;
            }
            .pagination-lg > li > a,
            .pagination-lg > li > span {
                padding: 9px 15px;
                font-size: 14px;
                line-height: 1.4285715;
            }
            .pagination-lg > li:first-child > a,
            .pagination-lg > li:first-child > span {
                border-bottom-left-radius: 5px;
                border-top-left-radius: 5px;
            }
            .pagination-lg > li:last-child > a,
            .pagination-lg > li:last-child > span {
                border-bottom-right-radius: 5px;
                border-top-right-radius: 5px;
            }
            .pagination-sm > li > a,
            .pagination-sm > li > span {
                padding: 6px 11px;
                font-size: 12px;
                line-height: 1.6666667;
            }
            .pagination-sm > li:first-child > a,
            .pagination-sm > li:first-child > span {
                border-bottom-left-radius: 2px;
                border-top-left-radius: 2px;
            }
            .pagination-sm > li:last-child > a,
            .pagination-sm > li:last-child > span {
                border-bottom-right-radius: 2px;
                border-top-right-radius: 2px;
            }

        </style>
