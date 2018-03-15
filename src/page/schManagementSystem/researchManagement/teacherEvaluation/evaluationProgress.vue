<template>
  <div class="g-container">
    <header class="g-textHeader">
      <div class="g-flexStartRow">
        <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
          <img src="../../../../assets/img/commonImg/icon_return.png" />
          返回流程图
        </el-button>
        <h2 class="selfCenter g-headerH">考评进度跟踪</h2>
      </div>
    </header>
    <section class="g-ep_echarts" id="j-ep-echarts"></section>
    <el-dialog class="dialogNotPadding headerNotBackground" :title="dialogTitle" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
      <div class="g-sectionR alertsList centerTable">
        <el-row>
          <el-table
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            :data="classesTimeSetTable" class="g-timeSettingTable">
            <el-table-column label="序号" type="index" width="80"></el-table-column>
            <el-table-column label="姓名" prop="name"></el-table-column>
          </el-table>
        </el-row>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    evaluationProgressLoad,//操作
  } from '@/api/http'
  import echarts from 'echarts';
  export default{
    data(){
      return{
        /*弹框*/
        isLoading:false,
        isDialog:false,
        dialogTitle:'',
        classesTimeSetTable:[],
        /*页面加载返回数据*/
        dataAjax:[],
        echartsData:[],
        /*send ajax param*/
        _id:'',
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      drawEcharts(id){
        this.isLoading=true;
        let _self=this;
        let chart=echarts.init(document.getElementById(id));
        let colors=['#4da1ff','#fca1d5'];
        chart.setOption({
          tooltip: {
            /*{a}、{b}、{c}、{d}，分别表示系列名，数据名，数据值，百分比*/
            trigger: 'item',
            formatter: "{a} <br/>{b}: {c} ({d}%)"
          },
          legend: {
            orient: 'horizontal',
            x: 'center',
            data:['已考评评委','未考评评委']
          },
          color:colors,
          series: [
            {
              /*最外层说明*/
              name:'访问来源',
              type:'pie',
              radius: ['40%', '55%'],
              label: {
                normal: {
                  textStyle: {
                    fontSize: 18
                  }
                }
              },
              labelLine: {
                normal: {
                  lineStyle: {
                    /**/
                  }
                }
              },
              data:_self.echartsData
            }
          ]
        });
        chart.on('click',function(params){
          _self.dialogTitle=params.name;
          _self.isDialog=true;
          _self.dataAjax.forEach((val)=>{
            if(val.name==params.name){
              _self.classesTimeSetTable=val.lists;
              _self.isLoading=false;
            }
          });
        });
      },
      /*关闭按钮点击*/
      handlerClose(done){
        done();
      },
      /*send ajax*/
      handlerEchart(){
        this.dataAjax.forEach((val)=>{
          this.echartsData.push({value:val.number,name:val.name});
        });
        /*画饼图*/
        this.$nextTick(()=>{
          this.drawEcharts('j-ep-echarts');
        });
      },
      getLoadAjax(){
        evaluationProgressLoad({id:this._id}).then(data=>{
          if(data.status){
            this.dataAjax=data.data;
            this.handlerEchart();
          }
          else{
            this.vmMsgError( '数据加载失败，请重试！' );
            this.dataAjax=[];
          }
        });
      },
    },
    created(){
      this._id=this.$route.params.id;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-ep_echarts{width:100%;height:500px;.marginTop(32);.marginBottom(20);}
</style>


