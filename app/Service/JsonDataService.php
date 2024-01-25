<?php

namespace App\Service;


class JsonDataService
{

    public $data;

    public $products;
    const month_keys=[
        "Январь",
        "Февраль",
        "Март",
        "Апрель",
        "Май",
    ];
    const sale_key="Продажи";
    const nazi_key="CC";

    public function __construct()
    {
        $this->data=json_decode(file_get_contents(base_path('resources\jsons\test.json')),true);



        foreach ($this->data[self::nazi_key] as $product){
            $this->products[$product["Наименование"] ?? "not_set"]["title"]=$product["Наименование"] ?? "not_set";
            $this->products[$product["Наименование"] ?? "not_set"]["category"]=$product["Категория товара"];
            $this->products[$product["Наименование"] ?? "not_set"]["base_cost"]=$product["Себестоимость"] ?? 0;
            $this->products[$product["Наименование"] ?? "not_set"]["sale_cost"]=$product["Рекомендуемая розничная цена"] ?? 0;
        }



    }

    public function getMonthsReport()
    {
        $result=[];
        foreach (self::month_keys as $month_key){
            $sale_count=0;
            $sale_amount=0;
            $result[$month_key]=[
                "sold_goods"=>0,
                "revenue_amount"=>0,
                "profit_amount"=>0,
                "average_sale"=>0,
            ];
            foreach ($this->data[$month_key] as $data){
                    if (isset($data["Наименование"]) && isset($this->products[$data["Наименование"]])){
                        $result[$month_key]["sold_goods"]+=$data["Число"] ?? 1;
                        $result[$month_key]["revenue_amount"]+=($data["Цена со скидкой"] ??$data["Цена"]) * ($data["Число"] ?? 1)?? 0 ;
                        $result[$month_key]["profit_amount"]+=$this->getProfit($data) ;
                    }
                    $sale_count++;
                    $sale_amount+=$data["Скидка"] ?? 0;
            }
            $result[$month_key]["average_sale"]=round($sale_amount/$sale_count);
        }

        return $result;
    }

    public function getNomenclatureReport()
    {
        $result=[];
        foreach (self::month_keys as $month_key){

            foreach ($this->data[$month_key] as $data){
                if (isset($data["Наименование"]) && isset($this->products[$data["Наименование"]])){
                    if (!isset($result[$data["Наименование"]])){

                        $result[$data["Наименование"]]["total"]=0;
                        $result[$data["Наименование"]]["product"]=$data["Наименование"];
                    }
                    if (!isset($result[$data["Наименование"]]["monthly"][$month_key])){
                        $result[$data["Наименование"]]["monthly"][$month_key]=0;
                    }
                    $result[$data["Наименование"]]["monthly"][$month_key]+=$this->getProfit($data);
                    $result[$data["Наименование"]]["total"]+=$this->getProfit($data);

                }
            }
        }
        usort($result, fn($a, $b) => $a['total'] < $b['total']);
        return array_slice($result, 0, 100, true);
    }

    public function getManagersReport()
    {
        $result=[];
        foreach (self::month_keys as $month_key){

            foreach ($this->data[$month_key] as $data){
                if (isset($data["Имя продавца"])&& !isset($result[$data["Имя продавца"]])){

                    $result[$data["Имя продавца"]]["total"]=0;
                    $result[$data["Имя продавца"]]["manager"]=$data["Имя продавца"];
                }
                if (isset($data["Наименование"]) && isset($this->products[$data["Наименование"]]) && isset($data["Имя продавца"])){

                    if (!isset($result[$data["Имя продавца"]]["monthly"][$month_key])){
                        $result[$data["Имя продавца"]]["monthly"][$month_key]=0;
                    }
                    $result[$data["Имя продавца"]]["monthly"][$month_key]+=$this->getProfit($data);
                    $result[$data["Имя продавца"]]["total"]+=$this->getProfit($data);

                }
            }
        }
        usort($result, fn($a, $b) => $a['total'] < $b['total']);
        return $result;
    }

    private function getProfit($data){
        if (!isset($data["Цена со скидкой"]) && !isset($data["Цена"])){
            return 0;
        }
        if (!isset($this->products[$data["Наименование"]])){
            return 0;
        }
        if (!isset($this->products[$data["Наименование"]]["base_cost"])){
            return 0;
        }
        $baseCost=$this->products[$data["Наименование"]]["base_cost"];
        $count=$data["Число"] ?? 1;

        return (($data["Цена со скидкой"] ?? $data["Цена"])* ($count))- ($baseCost*$count);

    }


}
