<?php
namespace Virtualorz\Faq;

use DB;
use Request;
use Validator;
use Virtualorz\Fileupload\Fileupload;
use App\Exceptions\ValidateException;
use PDOException;
use Exception;
use Pagination;
use Config;

class Faq
{
    public function list($page = 0) {

        $page_display = intval(Request::input('page_display', 10));
        if (!in_array($page_display, Config::get('pagination.data_display', []))) {
            $page_display = Config::get('pagination.items');
        }

        $qb = DB::table('faq')
            ->select([
                'faq.id',
                'faq.created_at',
                'faq.title',
                'faq.answer',
                'faq.enable',
                'cate.name as cate_name'
            ])
            ->leftJoin('cate','faq.cate_id','=','cate.id')
            ->whereNull('faq.delete')
            ->orderBy('faq.order');
        if($page !== 0)
        {
            $qb->offset(($page - 1) * $page_display)
                ->limit($page_display);
        }
        $dataSet = $qb->get();

        //多語言處理
        foreach($dataSet as $k=>$v)
        {
            $dataSet_lang = DB::table('faq_lang')
                ->select([
                    'faq_lang.lang',
                    'faq_lang.created_at',
                    'faq_lang.title',
                    'faq_lang.answer'
                ])
                ->where('faq_lang.faq_id',$v->id)
                ->get()
                ->keyBy('lang');
            $dataSet[$k]->lang = $dataSet_lang;
        }
        $dataCount = $qb->cloneWithout(['columns', 'orders', 'limit', 'offset'])
                ->cloneWithoutBindings(['select', 'order'])
                ->count();
            
        Pagination::setPagination(['total'=>$dataCount]);

        return $dataSet;
    }

    public function add()
    {
        $validator = Validator::make(Request::all(), [
            'faq-cate_id' => 'integer|required',
            'faq-title' => 'string|required|',
            'faq-answer' => 'string|required|',
            'faq-enable' => 'integer|required',
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors());
        }

        foreach (Request::input('faq-lang', []) as $k => $v) {
            $validator = Validator::make($v, [
                'faq-title' => 'string|required',
                'faq-answer' => 'string|required',
            ]);
            if ($validator->fails()) {
                throw new ValidateException($validator->errors());
            }
        }

        $dtNow = new \DateTime();

        DB::beginTransaction();
        try {
            $order = 1;
            //處理排序問題
            if(Request::input('faq-order',0) == -1)
            { //位於開頭
                DB::table('faq')
                    ->update([
                        'order' => DB::raw('`order` +1')
                    ]);
            }
            else if(Request::input('faq-order',0) == 0)
            {//位於最後一個
                $data_last = DB::table('faq')
                    ->select([
                        'faq.order'
                    ])
                    ->orderBy('order','desc')
                    ->first();
                if($data_last != null)
                {
                    $order = $data_last->order + 1;
                }
            }
            else
            {//位於誰後面
                $data_order = DB::table('faq')
                    ->select([
                        'faq.order'
                    ])
                    ->where('faq.id',Request::input('faq-order',0))
                    ->first();
                if($data_order != null)
                {
                    $order = $data_order->order + 1;
                }
                DB::table('faq')
                    ->where('faq.id','>',Request::input('faq-order',0))
                    ->update([
                        'order' => DB::raw('`order` +1')
                    ]);
            }

            $insert_id = DB::table('faq')
                ->insertGetId([
                    'cate_id' => Request::input('faq-cate_id'),
                    'created_at' => $dtNow,
                    'updated_at' => $dtNow,
                    'title' => Request::input('faq-title'),
                    'answer' => Request::input('faq-answer'),
                    'order' => $order,
                    'enable' => Request::input('faq-enable'),
                    'creat_admin_id' => Request::input('faq-creat_admin_id', null),
                    'update_admin_id' => Request::input('faq-update_admin_id', null),
                ]);
            
            foreach (Request::input('faq-lang', []) as $k => $v) {
                DB::table('faq_lang')
                    ->insert([
                        'faq_id' => $insert_id,
                        'lang' => $k,
                        'created_at' => $dtNow,
                        'updated_at' => $dtNow,
                        'title' => $v['faq-title'],
                        'answer' => $v['faq-answer'],
                        'creat_admin_id' => Request::input('faq-creat_admin_id', null),
                        'update_admin_id' => Request::input('faq-update_admin_id', null),
                    ]);
            }

            DB::commit();

        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }
    }

    public function edit()
    {
        $validator = Validator::make(Request::all(), [
            'faq-cate_id' => 'integer|required',
            'faq-title' => 'string|required|',
            'faq-answer' => 'string|required|',
            'faq-enable' => 'integer|required',
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors());
        }

        foreach (Request::input('faq-lang', []) as $k => $v) {
            $validator = Validator::make($v, [
                'faq-title' => 'string|required',
                'faq-answer' => 'string|required',
            ]);
            if ($validator->fails()) {
                throw new ValidateException($validator->errors());
            }
        }

        $dtNow = new \DateTime();

        DB::beginTransaction();
        try {
            $dataRow_before = DB::table('faq')
                ->select([
                    'faq.order',
                ])
                ->where('faq.id',Request::input('id'))
                ->first();

            $order = $dataRow_before->order;

            //處理排序問題
            if(Request::input('faq-order',0) == -1)
            { //位於開頭
                DB::table('faq')
                    ->where('faq.order','<',$order)
                    ->update([
                        'order' => DB::raw('`order` +1')
                    ]);
                $order = 1;
            }
            else if(Request::input('faq-order',0) != 0)
            {//位於誰後面
                $data_order = DB::table('faq')
                    ->select([
                        'faq.order'
                    ])
                    ->where('faq.id',Request::input('faq-order',0))
                    ->first();
                if(Request::input('faq-order',0) > $order)
                {
                    DB::table('faq')
                        ->where('faq.order','>',$order)
                        ->where('faq.order','<=',$data_order->order)
                        ->update([
                            'order' => DB::raw('`order` -1')
                        ]);
                    $order = $data_order->order;
                }
                else
                {
                    DB::table('faq')
                        ->where('faq.order','>',$data_order->order)
                        ->where('faq.order','<',$order)
                        ->update([
                            'order' => DB::raw('`order` +1')
                        ]);
                    $order = $data_order->order +1;
                }
                
            }
            
            
            DB::table('faq')
                ->where('id', Request::input('id'))
                ->update([
                    'cate_id' => Request::input('faq-cate_id'),
                    'updated_at' => $dtNow,
                    'title' => Request::input('faq-title'),
                    'answer' => Request::input('faq-answer'),
                    'order' => $order,
                    'enable' => Request::input('faq-enable'),
                    'update_admin_id' => Request::input('faq-update_admin_id', null),
                ]);
            foreach (Request::input('faq-lang', []) as $k => $v) {
                DB::table('faq_lang')
                    ->where('faq_id', Request::input('id'))
                    ->where('lang', $k)
                    ->update([
                        'updated_at' => $dtNow,
                        'title' => $v['faq-title'],
                        'answer' => $v['faq-answer'],
                        'update_admin_id' => Request::input('faq-update_admin_id', null),
                    ]);
            }

            DB::commit();

        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }
    }

    public function detail($id = '')
    {
        $dataRow_faq = collect();
        try {
            $dataRow_faq = DB::table('faq')
                ->select([
                    'faq.id',
                    'faq.cate_id',
                    'faq.created_at',
                    'faq.updated_at',
                    'faq.title',
                    'faq.answer',
                    'faq.enable',
                    'faq.update_admin_id',
                    'cate.name AS cate_name',
                ])
                ->LeftJoin('cate','faq.cate_id','=','cate.id')
                ->where('faq.id', $id)
                ->whereNull('faq.delete')
                ->first();
            if ($dataRow_faq != null) {
                $dataSet_lang = DB::table('faq_lang')
                    ->select([
                        'faq_lang.lang',
                        'faq_lang.created_at',
                        'faq_lang.updated_at',
                        'faq_lang.title',
                        'faq_lang.answer',
                    ])
                    ->where('faq_lang.faq_id', $dataRow_faq->id)
                    ->get()
                    ->keyBy('lang');
                $dataRow_faq->lang = $dataSet_lang;
            }
        } catch (\PDOException $ex) {
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }

        return $dataRow_faq;
    }

    public function delete()
    {
        $validator = Validator::make(Request::all(), [
            'id' => 'required', //id可能是陣列可能不是
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors());
        }

        $ids = Request::input('id', []);
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $dtNow = new \DateTime();

        DB::beginTransaction();
        try {
            foreach ($ids as $k => $v) {

                DB::table('faq')
                    ->where('id', $v)
                    ->update([
                        'delete' => $dtNow,
                    ]);
            }

            DB::commit();
        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }
    }

    public function enable($type = '')
    {
        if ($type !== '') {
            $validator = Validator::make(Request::all(), [
                'id' => 'required', //id可能是陣列可能不是
            ]);
            if ($validator->fails()) {
                throw new ValidateException($validator->errors());
            }

            $ids = Request::input('id', []);
            if (!is_array($ids)) {
                $ids = [$ids];
            }

            $dtNow = new \DateTime();

            DB::beginTransaction();
            try {
                foreach ($ids as $k => $v) {
                    DB::table('faq')
                        ->where('id', $v)
                        ->whereNull('delete')
                        ->update([
                            'enable' => $type,
                            'updated_at' => $dtNow,
                        ]);
                }
                DB::commit();
            } catch (\PDOException $ex) {
                DB::rollBack();
                throw new PDOException($ex->getMessage());
                \Log::error($ex->getMessage());
            } catch (\Exception $ex) {
                DB::rollBack();
                throw new Exception($ex);
                \Log::error($ex->getMessage());
            }
        }
    }
}
