<template>
  <div class="g-container">
    <header class="g-importCourseHeader">
      <div class="g-selectPadding">
        <div class="g-filterPart g-flexStartRow">
          <el-form :inline="true">
            <el-form-item label="问卷名称：">
              <el-select @change="chooseLatitude" v-model="createPlacementForm.questionId">
                <el-option v-for="(content,index) in NaireData" :key="index" :label="content.questionName" :value="content.questionId"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="统计维度：">
              <el-select @change="wdChange" v-model="createPlacementForm.roleId">
                <el-option v-for="(content,index) in WDOne" :key="index" :label="content.grade0rclass" :value="content.roleId"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item :label="WDTwo.title+'：'" v-show="createPlacementForm.roleId && WDTwo.title">
              <el-select @change="wdTwoChange" v-model="createPlacementForm.gradeId">
                <el-option v-for="(content,index) in WDTwo.value" :key="index" :label="content.grade0rclass" :value="content.gradeId"></el-option>
              </el-select>
            </el-form-item>

            <el-form-item :label="WDThree.title+'：'" v-show="createPlacementForm.gradeId && WDThree.title">
              <el-select multiple v-model="createPlacementForm.classId">
                <el-option v-for="(content,index) in WDThree.value" :key="index" :label="content.grade0rclass" :value="content.classId"></el-option>
              </el-select>
            </el-form-item>
          </el-form>
        </div>
        <div class="g-filterPart g-flexStartRow">
          <span style="padding-top: .75rem">筛选答案：</span>
          <div>
            <div v-for="(row,rowIndex) in filterAnswerForm.itemArr" class="g-flexStartRow g-filterRow">
              <el-select class="g-itemSelect" @change="filterAnswerAdd(rowIndex)" placeholder="请选择题目" v-model="row.QNum">
                <el-option v-for="(content,index) in NaireProblemData" :key="index" :label="content.title" :value="content.QNum"></el-option>
              </el-select>
              <!--单选或多选题型v-if处加一个为单选或多选题型的判断-->
              <el-select v-if="row.QNum && (row.type==0 || row.type==1)" class="g-itemSelect" v-model="row.answer">
                <el-option v-for="(ans,ansI) in radioAnswerData" :key="ansI" :label="ans" :value="ans"></el-option>
              </el-select>
              <!--问答题v-if处加一个为单选或多选题型的判断-->
              <el-select v-if="row.QNum && row.type==2" class="g-itemSelect" v-model="row.answer">
                <el-option label="包含" value="1"></el-option>
                <el-option label="不包含" value="0"></el-option>
              </el-select>
              <el-input v-if="row.QNum && row.type==2" class="g-itemSelect" v-model="row.value"></el-input>
              <!--分数题v-if处加一个为单选或多选题型的判断-->
              <el-select v-if="row.QNum && row.type==3" class="g-itemSelect" v-model="row.answer">
                <el-option label="=" value="3"></el-option>
                <el-option label="≠" value="4"></el-option>
                <el-option label="<" value="2"></el-option>
                <el-option label=">" value="1"></el-option>
              </el-select>
              <el-input v-if="row.QNum && row.type==3" class="g-itemSelect" v-model="row.value"></el-input>
              <el-button type="text" @click="deleteFilterClick(rowIndex)" class="g-itemSelect icon_shutDown"></el-button>
            </div>
            <div class="g-flexStartRow g-filterButtonGroup">
              <el-button type="primary" @click="createFilterClick" class="el-icon-plus g-imgContainer">添加筛选条件</el-button>
              <el-button @click="searchAjax" type="primary" class="el-icon-search">查询</el-button>
            </div>
          </div>
        </div>
      </div>
    </header>
    <section>
      <header class="g-textHeader g-flexStartRow">
        <h2 class="g-questionNaireTitle" v-text="questionName"></h2>
      </header>
      <div class="g-questionPart" v-for="(row,rowIndex) in totalData">
        <!--单选题或多选题-->
        <div class="g-QNEchartRow" v-if="row.type==0 || row.type==1">
          <!--头部信息-->
          <div class="g-liOneRow g-QNEchartRowHeader">
            <h3 class="selfCenter" v-text="'Q'+row.QNum+'：'+row.title"></h3>
            <div class="g-flexStartRow g-filterRow">
              <el-select class="g-itemSelect" @change="echartsChange(rowIndex)" v-model="echartsControll[rowIndex].echartsType">
                <el-option label="饼状图" value="pie"></el-option>
                <el-option label="柱状图" value="bar"></el-option>
                <el-option label="条形图" value="barChart"></el-option>
              </el-select>
              <el-select class="g-itemSelect" placeholder="显示设置" v-model="echartsControll[rowIndex].hiddenPart">
                <el-option label="隐藏图表" v-show="echartsControll[rowIndex].hiddenPart!=0" value="0"></el-option>
                <el-option label="显示图表" v-show="echartsControll[rowIndex].hiddenPart!=1" value="1"></el-option>
                <el-option label="隐藏数据表" v-show="echartsControll[rowIndex].hiddenPart!=2" value="2"></el-option>
                <el-option label="显示数据表" v-show="echartsControll[rowIndex].hiddenPart!=3" value="3"></el-option>
              </el-select>
            </div>
          </div>
          <!--图形展示-->
          <!--QNEchart0根据循环的index确定id-->
          <div :id="'QNEchart'+rowIndex" style="width:50%;height:23.75rem;" v-show="Number(echartsControll[rowIndex].hiddenPart)!=0"></div>
          <!--table表格-->
          <!--单选或多选题表格-->
          <table class="g-echartTable" v-show="Number(echartsControll[rowIndex].hiddenPart)!=2">
            <thead class="g-tableHeader">
              <tr><th>答案选项</th><th>回复情况（%）</th><th>回复情况（数值：人）</th></tr>
            </thead>
            <tbody class="g-tableContent">
              <tr v-for="(column,columnI) in row.data">
                <td v-text="column.title"></td>
                <td v-text="column.percentage"></td>
                <td v-text="column.count"></td>
              </tr>
              <tr>
                <td v-text="'受访人数：'+row.count" colspan="3"></td>
              </tr>
            </tbody>
          </table>
        </div>
        <!--问答题-->
        <div class="g-QNEchartRow" v-if="row.type==2">
          <!--头部信息-->
          <div class="g-liOneRow g-QNEchartRowHeader">
            <h3 class="selfCenter" v-text="'Q'+row.QNum+'：'+row.title"></h3>
          </div>
          <!--图形展示-->
          <!--table表格-->
          <!--问答题表格-->
          <table class="g-questionTable">
            <tbody class="g-tableContent">
            <tr>
              <td colspan="2">答案</td>
            </tr>
            <tr v-for="(column,columnI) in currentData[rowIndex]">
              <td colspan="2"><span v-text="(columnI+1)+'：'"></span><span v-text='column.title'></span></td>
            </tr>
            <tr>
              <td v-text="'受访人数：'+row.count"></td>
              <td class="g-alignRight">
                <span v-text="footerArr[rowIndex].currentPage"></span><span>/</span><span v-text="footerArr[rowIndex].totalPage"></span>
                <el-input v-model="footerArr[rowIndex].goPage" placeholder="页数" style="width:5rem;"></el-input>
                <el-button @click="goPageClick(rowIndex)" type="text">跳转</el-button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <!--分数题-->
        <div class="g-QNEchartRow" v-if="row.type==3">
          <!--头部信息-->
          <div class="g-liOneRow g-QNEchartRowHeader">
            <h3 class="selfCenter" v-text="'Q'+row.QNum+'：'+row.title"></h3>
            <div class="g-flexStartRow g-filterRow">
              <el-select class="g-itemSelect" @change="echartsChange(rowIndex)" v-model="echartsControll[rowIndex].echartsType">
                <el-option label="柱状图" value="bar"></el-option>
                <el-option label="条形图" value="barChart"></el-option>
                <el-option label="折线图" value="line"></el-option>
                <el-option label="雷达图" value="radar"></el-option>
              </el-select>
              <el-select class="g-itemSelect" placeholder="显示设置" v-model="echartsControll[rowIndex].hiddenPart">
                <el-option label="隐藏图表" v-show="echartsControll[rowIndex].hiddenPart!=0" value="0"></el-option>
                <el-option label="显示图表" v-show="echartsControll[rowIndex].hiddenPart!=1" value="1"></el-option>
                <el-option label="隐藏数据表" v-show="echartsControll[rowIndex].hiddenPart!=2" value="2"></el-option>
                <el-option label="显示数据表" v-show="echartsControll[rowIndex].hiddenPart!=3" value="3"></el-option>
              </el-select>
            </div>
          </div>
          <!--图形展示-->
          <!--QNEchart0根据循环的index确定id-->
          <div :id="'QNEchart'+rowIndex" style="width:50%;height:23.75rem;" v-show="Number(echartsControll[rowIndex].hiddenPart)!=0"></div>
          <!--table表格-->
          <!--分数题表格-->
          <table class="g-scoreTable" v-show="Number(echartsControll[rowIndex].hiddenPart)!=2">
            <tbody class="g-tableContent">
            <tr>
              <th>分数范围</th>
              <td v-for="(column,columnI) in row.data" v-text="column.title"></td>
              <td>平均分</td>
            </tr>
            <tr>
              <th>回复情况（%）</th>
              <td v-for="(column,columnI) in row.data" v-text="column.percentage"></td>
              <td rowspan="2" v-text="row.avg"></td>
            </tr>
            <tr>
              <th>回复情况（数值：人）</th>
              <td v-for="(column,columnI) in row.data" v-text="column.count"></td>
            </tr>
            <tr>
              <td v-text="'受访人数：'+row.count" colspan="7"></td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</template>
<script>
  import {
    echartsStatisticQuestionN,//问卷名称
    echartsStatisticWD,//统计维度
    echartsStatisticNaire,//得到题目——筛选条件
    echartsStatisticLoad,//展示信息
  } from '@/api/http'
  import echarts from 'echarts'
  import ChartOptionSet from '@/assets/js/echartOption'
  export default{
    data(){
      return{
        /*图标控制*/
        /*数据初始化时，遍历数据个数，根据数据个数为echartsControll添加n个对象元素*/
        /*图表信息切换与隐藏部分，在发送请求获取页面数据时需要初始化echartsControll内容，
        有相应个数的控制对象*/
        echartsControll:[],
        /*文件名*/
        questionName:'',
        echartsData:[],
        /*问答题分页*/
        footerArr:[],
        pageSize:5,
        currentData:[],//问答题绑定在页面上的值
        /*问卷名称等条件*/
        WDTwoSelect:null,
        createPlacementForm:{
          questionId:'',
          roleId:'',
          gradeId:'',
          classId:[],
        },
        /*筛选答案条件*/
        filterAnswerForm:{
          itemArr:[],
        },
        /*筛选条件单个*/
        /*问卷名称*/
        NaireData:[],
        /*筛选答案的标题*/
        NaireProblemData:[],
        /*筛选答案为单选题或多选题时的答案*/
        radioAnswerData:[],
        /*页面请求数据*/
        totalData:[],
        /*统计维度*/
        WDTotal:{},
        WDOne:[],
        WDTwo:{title:'',check:false,value:[]},
        WDThree:{title:'',value:[]},
      }
    },
    computed: {},
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'newStudentClass'});
      },
      /*table*/
      /*查询条件*/
      /*添加筛选答案type为选择类型根据后台返回数据定，range与answer也根据后台参数名定*/
      createFilterClick(){
        this.filterAnswerForm.itemArr.push({QNum:'',value:'',type:''});
      },
      /*根据题目确定后方的内容*/
      filterAnswerAdd(index){
        /*确定选择的题型*/
        let _self=this;
        for(let i=0;i<this.NaireProblemData.length;i++){
          if(_self.filterAnswerForm.itemArr[index].QNum==_self.NaireProblemData[i].QNum){
            /*单选或多选，赋值答案*/
            if(_self.NaireProblemData[i].type==0 || _self.NaireProblemData[i].type==1){
              _self.radioAnswerData=[];
              _self.NaireProblemData[i].QNSetting.forEach((asw)=>{
                _self.radioAnswerData.push(asw.value);
              });
            }
            else{
            }
            this.$set(this.filterAnswerForm.itemArr[index],'answer','');
            _self.filterAnswerForm.itemArr[index].type=_self.NaireProblemData[i].type;
            break;
          }
        }
      },
      /*删除筛选答案*/
      deleteFilterClick(index){
        this.filterAnswerForm.itemArr.splice(index,1);
      },
      /*选择图表类型select框的change事件*/
      echartsChange(index){
        if(this.echartsControll[index].echartsType=='bar'){
          /*柱状图*/
          let option = ChartOptionSet.ChartOptionTemplates.Bar(this.totalData[index].data,'回答人数（人）','title','count');
          this.echartsData[index].setOption(option);
        }
        else if(this.echartsControll[index].echartsType=='barChart'){
          /*条形图*/
          let option = ChartOptionSet.ChartOptionTemplates.BarChart(this.totalData[index].data,'回答人数（人）','title','count');
          this.echartsData[index].setOption(option);
        }
        else if(this.echartsControll[index].echartsType=='pie'){
          /*饼状图*/
          let option = ChartOptionSet.ChartOptionTemplates.Pie(this.totalData[index].data,'回答人数（人）','title','count');
          this.echartsData[index].setOption(option);
        }
        else if(this.echartsControll[index].echartsType=='line'){
          /*折线图*/
          let option = ChartOptionSet.ChartOptionTemplates.Line(this.totalData[index].data,'回答人数（人）','title','count');
          this.echartsData[index].setOption(option);
        }
        else if(this.echartsControll[index].echartsType=='radar'){
          /*雷达图*/
          let option = ChartOptionSet.ChartOptionTemplates.Radar(this.totalData[index].data,'回答人数（人）','title','count');
          this.echartsData[index].setOption(option);
        }
      },
      /*得到数据后得到每道题的echarts对象（只能取一次），并重新setOption*/
      echartsInit(){
        this.echartsData=[];
        /*创建每道题的echarts实例*/
        this.totalData.forEach((row,rowI)=>{
          let container=$('#QNEchart'+rowI)[0];
          /*排除问答题*/
          if(container){
            this.echartsData.push(echarts.init(container));
          }
          else{
            this.echartsData.push('');
          }
        });
        /*画图*/
        this.echartsData.forEach((row,rowI)=>{
          let tempObj = this.totalData[rowI];
          if(row){
            /*排除问答题——问答题是空字符串，不是问答题是Echart对象*/
            let option;
            if( tempObj.type == 0 ){
              option = ChartOptionSet.ChartOptionTemplates.Pie( tempObj.data,'回答人数（人）','title','count');
            }
            else if( tempObj.type == 1 ){
              option = ChartOptionSet.ChartOptionTemplates.BarChart( tempObj.data,'回答人数（人）','title','count');
            }
            else if( tempObj.type == 3 ){
              option = ChartOptionSet.ChartOptionTemplates.Bar( tempObj.data,'回答人数（人）','title','count');
            }
            row.setOption(option);
          }
        });
      },
      /*问答题跳转页数*/
      goPageClick(idx){
        /*判断页数的合法性*/
        if(this.footerArr[idx].goPage){
          /*判断是否为空*/
          if(this.footerArr[idx].goPage<=0){
            this.footerArr[idx].goPage=1;
          }
          else if(this.footerArr[idx].goPage>this.footerArr[idx].totalPage){
            this.footerArr[idx].goPage=this.footerArr[idx].totalPage;
          }
        }
        else{
          this.footerArr[idx].goPage=1;
        }
        this.pageGoChange(idx);
      },
      /*问答题页数跳转*/
      pageGoChange(idx){
        /*修改当前页数*/
        this.footerArr[idx].currentPage=this.footerArr[idx].goPage;
        let _go=this.footerArr[idx].goPage-1;
        /*数据变化*/
        this.currentData[idx].splice(0,this.currentData[idx].length,...this.totalData[idx].data.slice(_go*this.pageSize,(_go+1)*this.pageSize));
      },
      /*维度change事件*/
      wdChange(newVal){
        this.createPlacementForm.gradeId = '';
        this.createPlacementForm.classId = [];
        this.WDTwo.title=this.WDTotal.tow[newVal].title;
        this.WDTwo.value=this.WDTotal.tow[newVal].value;
        this.WDTwo.check=this.WDTotal.tow[newVal].check;
      },
      /*维度的第二个标签change事件*/
      wdTwoChange(newVal){
          this.createPlacementForm.classId = [];
          this.WDThree.title = this.WDTotal.three[newVal] && this.WDTotal.three[newVal].title;
          this.WDThree.value = this.WDTotal.three[newVal] && this.WDTotal.three[newVal].value;
      },
      /*send ajax*/
      /*问卷名称change*/
      chooseLatitude(){
        this.getLaletude();
        this.getNaireProblem();
        this.filterAnswerForm.itemArr=[];
        this.createPlacementForm.roleId = '';
        this.createPlacementForm.gradeId = '';
        this.createPlacementForm.classId = [];
      },
      /*得到筛选答案的题目*/
      getNaireProblem(){
        echartsStatisticNaire({questionId:this.createPlacementForm.questionId}).then(data=>{
          if(data.statu){
            this.NaireProblemData=data.data;
          }
          else{
            this.NaireProblemData=[];
            this.vmMsgError( '问卷题目数据加载失败，请重试！' );
          }
        });
      },
      /*得到问卷名称*/
      getNaireMsg(){
        echartsStatisticQuestionN().then(data=>{
          if(data.statu){
            this.NaireData=data.data;
            /*默认第一条*/
            if(this.NaireData.length>0){
              this.createPlacementForm.questionId=this.NaireData[0].questionId;
              this.chooseLatitude();
            }
          }
          else{
            this.NaireData=[];
            this.vmMsgError( '问卷名称数据加载失败，请重试！' );
          }
        });
      },
      /*得到维度*/
      getLaletude(){
        this.createPlacementForm.roleId='';
        echartsStatisticWD({questionId:this.createPlacementForm.questionId}).then(data=>{
          if(data.statu){
            this.WDOne=data.data.one;
            this.WDTotal=data.data;
          }
          else{
            this.WDOne=[];
            this.vmMsgError( '统计维度数据加载失败，请重试！' );
          }
        });
      },
      /*查询*/
      searchAjax(){
        echartsStatisticLoad({...this.createPlacementForm,serialNumData:this.filterAnswerForm.itemArr}).then(data=>{
          if(data.statu){
            this.hanlderData(data.data);
            this.totalData=data.data;
            this.questionName=data.title;
            this.$nextTick(()=>{
              this.echartsInit();
            });
          }
          else{
            this.vmMsgError( '查询失败，请重试！' );
            this.totalData=[];
            this.questionName='';
          }
        });
      },
      hanlderData(data){
        if( !data.length ){
          this.vmMsgWarning( '暂无数据！' ); return;
        }
        this.echartsControll=[];
        this.footerArr=[];
        this.currentData=[];
        data.forEach((val,i)=>{
          /*问答题添加footer*/
          if( val.type == 2 ){
            /*为问答题加footer*/
            this.footerArr.push({currentPage:1,totalPage:Math.ceil(val.data.length/this.pageSize),goPage:1});
            let _go=this.footerArr[i].goPage-1;
            this.currentData.push(data[i].data.slice(_go*this.pageSize,(_go+1)*this.pageSize));
            /*除问答题外*/
            this.echartsControll.push('');
          }
          else if( val.type == 0 ){
            this.echartsControll.push({echartsType:'pie', hiddenPart:'显示设置'});
          }
          else if(val.type == 1 ){
            this.echartsControll.push({echartsType:'barChart', hiddenPart:'显示设置'});
          }
          else if( val.type == 3 ){
            this.echartsControll.push({echartsType:'bar', hiddenPart:'显示设置'});
          }

          if( val.type != 2 ){
            this.footerArr.push('');
            this.currentData.push('');
          }
        });
      },
    },
    created(){
      this.getNaireMsg();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/style';
  div.g-container{padding:0;margin-right:0;width:100%;}
  .g-importCourse .g-container .g-section{margin:1.25rem 0;width:100%;}
  header.g-importCourseHeader{padding:0;}
  /*弹框*/
  .headerNotBackground{
    .dialogHeader{position:absolute;right:20px;top:0.625rem;
      button{.border-radius(1rem);}
    }
    .dialogSection{
      .NotAllWidth{width:auto;}
      .defineInputWidth{.widthRem(60);}
    }
  }
  .g-flexStartRow{
    span{padding-right:20/16rem;}
  }
  .g-selectPadding{padding:15/16rem 0 0;}

  /*上方筛选*/
  .g-filterRow{.marginBottom(20);}
  .g-selectGroup:not(:first-of-type){margin-left:20/16rem;}
  .g-itemSelect:not(:first-of-type){margin-left:20/16rem;}
  .g-itemSelect.icon_shutDown:before{padding-left:10/16rem;color:@buttonActiveR;font-weight:bold;}
  .g-filterButtonGroup{.marginBottom(20);}

  /*问卷*/
  .g-questionNaireTitle{text-align:center;padding:30/16rem 0;width:100%;}
  /*图表*/
  div[id*='QNEchart']{.NotLineheight(380);width:50%;margin:0 auto;}
  /*单选多选题*/
  .g-echartTable{.widthRem(750);margin:0 auto;border-collapse:collapse;border-spacing:0;margin-top:20/16rem;
    .g-tableContent{display: block;width:100%;.NotLineheight(200);overflow-y:auto;overflow-x:hidden;}
    .g-tableHeader{display: block;width:100%;}
    tr{border-left:1px solid @borderColor;border-top:1px solid @borderColor;}
    tr:last-of-type{border-bottom:1px solid @borderColor;border-right:1px solid @borderColor;}
    th,td{.widthRem(250);padding:0.5rem;.fontSize(14);text-align:left;}
  }
  /*分数题*/
  .g-scoreTable{.widthRem(750);margin:0 auto;border-collapse:collapse;border-spacing:0;margin-top:20/16rem;
    .g-tableContent{display: block;width:100%;overflow-x:auto;overflow-y:hidden;}
    tr{border-left:1px solid @borderColor;border-top:1px solid @borderColor;}
    tr:last-of-type{border-bottom:1px solid @borderColor;border-right:1px solid @borderColor;}
    th,td{.widthRem(250);padding:0.5rem;.fontSize(14);text-align:left;border:1px solid @borderColor;}
  }
  /*问答题*/
  .g-questionTable{.widthRem(750);margin:0 auto;border-collapse:collapse;border-spacing:0;margin-top:20/16rem;
    /*.g-tableContent{display: block;width:100%;overflow-x:auto;overflow-y:hidden;}*/
    tr{border-left:1px solid @borderColor;border-top:1px solid @borderColor;}
    tr:last-of-type{border-bottom:1px solid @borderColor;}
    td:last-of-type{border-right:1px solid @borderColor;}
    th,td{.widthRem(250);padding:0.5rem;.fontSize(14);text-align:left;word-break:break-all;}
    td.g-alignRight{text-align:right;}
  }
  /*每道题最外层*/
  .g-questionPart{padding:20/16rem 0;}
/*  .g-tableHeader span{width:50%;.height(25);.fontSize(14);padding:5/16rem;border-top:1px solid @borderColor;border-left:1px solid @borderColor;.box-sizing();font-weight:bold;display:inline-block;}
  .g-tableHeader span:last-of-type{border-right:1px solid @borderColor;}*/
</style>








