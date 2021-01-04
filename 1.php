<?php

/**
     * 渠道分析报表
     * @Author 王伟涛<wang.weitao1@byd.com>
     * @Date 2020-12-03 15:11:39
     */
    public function channel()
    {
        if (Request::isAjax()) {
            $orgid = session('curid');
            //实例会话列表模型
            $userSessionModel = new UserSession();
            //会话记录模型
            $session_detail = new SessionDetail();

            //获取参数
            $param = [
                'start_date' => input('param.start_date', ''),
                'end_date' => input('param.end_date', '')
            ];

            //条件查询
            $where = [['create_time', 'between', [date('Y-m-d', time()), date("Y-m-d", strtotime("+1 day"))]]];//date('Y-m-d', time())
            
            if ($param['start_date']) {
                $where = [['create_time', '>', $param['start_date']]];
            }

            if ($param['end_date']) {
                $where = [['create_time', '<', $param['end_date']]];
            }

            if ($param['start_date'] && $param['end_date']) {
                if ($param['start_date'] == $param['end_date']){
                    $where = [['create_time', 'between', [$param['start_date'], date('Y-m-d', strtotime($param['end_date'])+86400)]]];
                } else {
                    $where = [['create_time', 'between', [$param['start_date'], $param['end_date']]]];
                }
            }

            $data = [];
            //1,微信公众号，2,微信小程序 , 3，H5, 4,小程序插件接入
            for ($i = 1; $i <= 4; $i++) {
                //渠道id集合
                $channels = Access::where(['type_id' => $i, 'oid' => $orgid])->column('id');

                //会话记录
                $result = $userSessionModel->alias('us')->where($where)->whereIn('access_id', $channels)->field('id,u_cust_id,cust_id,create_time,access_id,org_id')->select();

                //访客数
                $visitors = count(array_unique($result->column('u_cust_id')));

                //人工接入量
                $artificial = $result->where('state', '!=', 6)->count();

                //人工消息总量
                $artificial_msg = $session_detail->whereIn('sess_id', $result->column('id'))->where($where)->where('send_from', 2)->count('id');
                //人工有效会话量
                $artificial_valid = $result->where('us.response_time', '<', 'us.user_update_time')->count();

                //最终渠道数据
                $data[$i] = [
                    'count' => $result->count(),    //总会话数
                    'visitors' => $visitors,    //访客数量
                    'machine' => count(array_unique($result->column('robot_id'))), //$result->where('state', 6)->count(),   //机器人量
                    'machine_msg' => $session_detail->where($where)->where('send_from', 5)->count('id'),//机器人消息量
                    'artificial' => $artificial,    //人工接入量
                    'artificial_rate' => $artificial ? round($artificial / $visitors, 2) : 0, //人工转化率
                    'artificial_msg' => $artificial_msg,  //人工消息总量
                    'artificial_invalid' => $result->where('us.response_time', '>', 'us.user_update_time')->count(),  //人工无效会话量
                    'artificial_valid' => $artificial_valid,   //人工有效会话量
                    'artificial_valid_rate' => $artificial_valid && $artificial_msg ? round($artificial_valid / $artificial_msg, 2) : 0//人工有效接待率
                ];
            }
            return $this->returnJson(200, '获取成功', $data);
        }

        return view();
    }