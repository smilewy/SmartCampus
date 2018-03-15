<template>
  <div class="g-container">
    <header class="g-textHeader g-importCourseHeader">
      <div class="g-flexStartRow g-selectPadding">
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
             <el-form-item :label="WDTwo.title+'：'" v-show="createPlacementForm.roleId">
              <el-select @change="wdTwoChange" v-model="WDTwoSelect">
                <el-option v-for="(content,index) in WDTwo.value" :key="index" :label="content.grade0rclass" :value="content.wynum"></el-option>
              </el-select>
             </el-form-item>
             <el-form-item :label="WDThree.title+'：'" v-show="createPlacementForm.roleId && WDTwo.check && WDTwoSelect">
              <el-select multiple v-model="createPlacementForm.classId">
                <el-option v-for="(content,index) in WDThree.value" :key="index" :label="content.grade0rclass" :value="content.classId"></el-option>
              </el-select>
             </el-form-item>
             <el-form-item label="过滤重复IP：">
              <el-switch v-model="createPlacementForm.ifIp" active-text="是" inactive-text="否" active-color="#13b5b1" inactive-color=""></el-switch>
             </el-form-item>
             <el-form-item>
               <el-button type="primary" @click="searchAjax" class="radiusButton selfTop" icon="el-icon-search">查询</el-button>
             </el-form-item>
           </el-form>

        </div>
        <!-- <el-button type="primary" @click="searchAjax" class="radiusButton selfTop" icon="el-icon-search">查询</el-button> -->
      </div>
    </header>
    <div class="g-container g-containerNoPadding">
      <section class="g-section">
        <div class="gs-table centerTable alertsList">
          <el-table class="g-NotHover"
                    ref="studentMsgTable"
                    v-loading="loading"
                    element-loading-text="拼命加载中"
                    element-loading-spinner="el-icon-loading"
                    :data="headerButtonData.studentBasicMsg.data"
                    style="width:100%"
                    @sort-change="sortChange"
                    @selection-change="handleStudentTable">
            <!--show-overflow-tooltip 超出部分省略号显示-->
            <el-table-column label="姓名" prop="name" sortable="custom"></el-table-column>
            <el-table-column label="提交问卷时间" prop="fillTime" sortable="custom"></el-table-column>
            <!--<el-table-column label="所用时间" prop="name" sortable=custom></el-table-column>-->
            <el-table-column label="年级班级" prop="name" sortable="custom">
              <template slot-scope="prop">
                <span v-if="prop.row.grade && prop.row.className" v-text="gradeData[prop.row.grade]+prop.row.className+'班'"></span>
                <span v-else-if="prop.row.className" v-text="prop.row.className+'班'"></span>
                <span v-else-if="prop.row.grade" v-text="gradeData[prop.row.grade]"></span>
              </template>
            </el-table-column>
            <el-table-column label="部门" prop="department" sortable="custom"></el-table-column>
            <el-table-column label="操作" fixed="right">
              <template slot-scope="props">
                <el-button type="text" @click="scanQNaire(props.row.ifOut,props.row.id)">查看问卷</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </section>
    </div>
    <footer class="g-footer">
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          layout="prev, pager, next, jumper"
          :page-count="pageAll">
        </el-pagination>
      </el-row>
    </footer>
  </div>
</template>
<script>
  import {
    replyDetailLoad,//查询
    echartsStatisticWD,//统计维度
    echartsStatisticQuestionN,//问卷名称
  } from '@/api/http'
  export default{
    data(){
      return{
        /*ajax data*/
        headerButtonData:{
          studentBasicMsg:[],
        },
        gradeData:['一年级','二年级','三年级','四年级','五年级','六年级','初一','初二',
          '初三','高一','高二','高三'
        ],
        /*footer*/
        pageAll:1,
        currentPage:1,
        pageCount:10,
        priority:'',
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*问卷名称等条件*/
        WDTwoSelect:null,
        createPlacementForm:{
          questionId:'',
          roleId:'',
          gradeId:'',
          classId:[],
          ifIp:'',
        },
        /*统计维度*/
        WDTotal:{},
        WDOne:[],
        WDTwo:{title:'',check:false,value:[]},
        WDThree:{title:'',value:[]},
        /*问卷名称*/
        NaireData:[],
        /*send ajax param*/
        sortData:{
          sort:'',
          sortData:'',
        },
        loading: false
      }
    },
    computed: {},
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'newStudentClass'});
      },
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.searchAjax();
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
      },
      sortChange(val){
        /*table排序回调*/
        this.sortData.sort=val.order;
        this.sortData.sortData=val.prop;
      },
      /*查看问卷*/
      scanQNaire(ifOut,id){
        this.$router.push({name:'scanQuestionNaire',params:{id:'1-'+this.createPlacementForm.questionId+'-'+ifOut+'-'+id}});
      },
      /*header的button群*/
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
      },
      /*维度change事件*/
      wdChange(newVal){
        this.WDTwoSelect=null;//第二个维度select绑定数据
        this.WDThree.classId=[];
        this.WDTwo.title=this.WDTotal.tow[newVal].title;
        this.WDTwo.value=this.WDTotal.tow[newVal].value;
        this.WDTwo.check=this.WDTotal.tow[newVal].check;
      },
      /*维度的第二个标签change事件*/
      wdTwoChange(newVal){
        if(this.WDTwo.check && newVal){
          this.WDThree.title=this.WDTotal.three[newVal].title;
          this.WDThree.value=this.WDTotal.three[newVal].value;
        }
        else{
          return false;
        }
      },
      /*问卷名称change*/
      chooseLatitude(){
        this.getLaletude();
      },
      /*send ajax*/
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
        this.loading = true;
        if(this.WDTwoSelect){
          this.createPlacementForm.gradeId=this.WDTwoSelect.gradeId;
        }
        else{
          this.createPlacementForm.gradeId='';
        }
        this.createPlacementForm.ifIp=Number(this.createPlacementForm.ifIp);
        replyDetailLoad({...this.createPlacementForm,...this.sortData,page:this.currentPage,pageSize:this.pageCount}).then(data=>{
          this.loading = false;
          if(data.statu){
            this.headerButtonData.studentBasicMsg=data.data;
            this.pageAll=this.headerButtonData.studentBasicMsg.maxPage;
          }
          else{
            this.vmMsgError( '查询失败，请重试！' );
            this.headerButtonData.studentBasicMsg=[];
          }
        });
      },
      hanlderData(data){
        this.echartsControll=[];
        this.footerArr=[];
        this.currentData=[];
        data.forEach((val,i)=>{
          /*问答题添加footer*/
          if(val.type==2){
            /*为问答题加footer*/
            this.footerArr.push({currentPage:1,totalPage:Math.ceil(val.data.length/this.pageSize),goPage:1});
            let _go=this.footerArr[i].goPage-1;
            this.currentData.push(data[i].data.slice(_go*this.pageSize,(_go+1)*this.pageSize));
            /*除问答题外*/
            this.echartsControll.push('');
          }
          else{
            this.footerArr.push('');
            this.currentData.push('');
            /*为除问答题外加图形控制*/
            this.echartsControll.push({echartsType:'bar',hiddenPart:'显示设置'});
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
  .g-container header.g-importCourseHeader{padding:0;}
  .g-importCourseHeader{margin-bottom:20/16rem;}
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
  .g-flexStartRow{.fontSize(14);
    /*<!--span{padding-left:30/16rem;.fontSize(14);}-->*/
  }
  .g-flexStartRow.g-selectPadding{padding:20/16rem 0 0;}

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
  .radiusButton{margin-left:20/16rem;}
</style>








