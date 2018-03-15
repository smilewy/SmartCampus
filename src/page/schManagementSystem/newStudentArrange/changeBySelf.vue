<template>
  <div class="g-container">
    <header class="g-textHeader g-importCourseHeader">
      <div class="g-flexStartRow">
        <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
          <img src="../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回流程图
        </el-button>
        <h2 class="selfCenter">手动调整</h2>
      </div>
      <div class="g-chooseButton g-flexStartRow">
        <span class="selfCenter">调整方式:</span>
        <div class="activeCss" data-msg="assign" @click="changeTypeClick">指定学生到班</div>
        <div class="normalCss" data-msg="equal" @click="changeTypeClick">相邻分数学生互换</div>
      </div>
    </header>
    <div class="g-containerNoPadding">
      <section class="g-section g-liOneRow">
        <div class="g-joinClassPart">
          <header class="g-textHeader">当前人数/容纳人数</header>
          <ul class="g-joinClassContainer g-liOneWrapRow">
            <li v-for="(content,n) in liRowData" @click="choose(n)">
              <div @click="chooseClassClick" :data-msg="content.classId" class="normalBoxShadow">
                <h2><span v-text="content.realNumber"></span><span>/</span><span v-text="content.number"></span></h2>
                <p v-text="content.class+content.level"></p>
              </div>
              <el-button class="examRadiusButton radiusButton" :disabled="!content.checked" @click="joinInClass(content)" type="primary">加入班级</el-button>
            </li>
          </ul>
        </div>
        <div class="g-tableH centerTable alertsList">
          <header class="g-liOneRow">
            <h2>参与分班新生名单（除去选中班级新生）</h2>
            <div class="gs-refresh selfCenter g-fuzzyInput">
              <el-input type="text" placeholder="姓名" v-model="fuzzyInput" suffix-icon="el-icon-search" @change="getPersonAjax"></el-input>
            </div>
          </header>
          <el-table
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            class="g-NotHover" border ref="studentMsgTable" :max-height="600" :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange" @selection-change="handleStudentTable">
            <!--show-overflow-tooltip 超出部分省略号显示-->
            <el-table-column type="selection" width="55px"></el-table-column>
            <el-table-column label="序号" width="80" type="index"></el-table-column>
            <el-table-column label="姓名" prop="name"></el-table-column>
            <el-table-column label="性别" prop="sex"></el-table-column>
            <el-table-column label="合成总分" prop="score"></el-table-column>
            <el-table-column label="班级" prop=""></el-table-column>
            <el-table-column label="指定班级" prop="name">
              <template slot-scope="prop">
                <span v-if="Number(prop.row.assign)">是</span>
                <span v-else>否</span>
              </template>
            </el-table-column>
            <el-table-column label="排名" prop="rank"></el-table-column>
          </el-table>
        </div>
      </section>
    </div>
  </div>
</template>
<script>
  import {
    changeBySelfLoad,//加载信息
    newStudentGetGrade,//参与分班学生名单
    changeBySelfAssign,//指定学生到班
    changeBySelfEqual,//相邻学生互换
  } from '@/api/http'
  export default{
    data(){
      return{
        /*ajax data*/
        /*table*/
        isLoading:false,
        myChecked:true,
        headerButtonData:{
          studentBasicMsg:[],
        },
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*table*/
        multipleSelect:[],
        /*左边信息*/
        liRowData:[],
        /*send ajax param*/
        gradeId:'',
        classId:'',
        className:'',
        ajaxType:'assign',
        ids:[],
        preClass:[],
      }
    },
    computed: {},
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'newStudentClass'});
      },
      /*调整方式*/
      changeTypeClick(event){
        let e=$(event.currentTarget);
        this.ajaxType=e.data('msg');
        this.changeCss(e,'activeCss','normalCss');
      },
      /*加入班级*/
      joinInClass(row){
        this.classId=row.classId;
        this.className=row.class;
        this.handlerParam();
        if(this.ids.length>0){
            let names = this.ids.map( o => o.name );
            let tip = "确定要将学生【" + (names.join('，')) + "】加入" + row.class + "班，与该班级学生进行互换吗？";
            this.$confirm( tip, "提示", {
                showConfirmButton: true,
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning',
            })
            .then( this.assginStudentAjax )
            .catch( ( e ) => {
                if( e != 'cancel' ) {
                    this.vmMsgError( e || e.message );
                }
            })
        }
        else{
          this.vmMsgWarning('请选择需要加入班级的学生！');
        }
      },
      handlerParam(){
        this.ids=[];this.preClass=[];
        if(this.ajaxType=='assign'){
          /*指定学生到班*/
          this.multipleSelect.forEach((row,n)=>{
            this.ids.push({id:row.id,stuId:row.stuId,userId:row.userId});
          });
        }
        else if(this.ajaxType=='equal'){
          /*相邻分数互换*/
          this.multipleSelect.forEach((row,n)=>{
            this.ids.push({id:row.id, name: row.name, stuId:row.stuId,userId:row.userId,serialNumber:row.serialNumber,rank:row.rank});
            this.preClass.push({[row.id]:{classId:row.classId,class:row.class}});
          });
        }
      },
      choose(idx){
        this.liRowData.forEach((val,subIdx)=>{
            val.checked=subIdx===idx;
        });
      },
      /*班级选择*/
      chooseClassClick(event){
        let e=$(event.currentTarget);
        this.classId=e.data('msg');
        this.getPersonAjax();
        e.removeClass('normalBoxShadow');
        e.parent().siblings().find('div').removeClass('activeBoxShadow');
        e.parent().siblings().find('div').addClass('normalBoxShadow');
        e.addClass('activeBoxShadow');
      },
      changeCss(e,active,normal){
        e.removeClass(normal);
        e.siblings().removeClass(active);
        e.addClass(active);
        e.siblings().addClass(normal);
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
        this.multipleSelect=section;
      },
      sortChange(column){
        /*table排序回调*/
      },
      /*编辑*/
      changeClick(index){
        this.isDialog=true;
        this.dialogTitle='编辑信息';
      },
      /*header的button群*/
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
      },
      /*send ajax*/
      getLoadAjax(){
        changeBySelfLoad({gradeId:this.gradeId}).then(data=>{
          if(data.status){
            this.liRowData=data.data;
          }
          else{
            this.liRowData=[];
            this.vmMsgError('暂无数据');
          }
        });
      },
      /*得到分班学生名单*/
      getPersonAjax(){
        this.isLoading=true;
        newStudentGetGrade({func:'classStu',param:{gradeId:this.gradeId,classId:this.classId,key:this.fuzzyInput}}).then(data=>{
          if(data.status){
            this.headerButtonData.studentBasicMsg=data.data;
          }
          else{
            this.headerButtonData.studentBasicMsg=[];
            this.vmMsgError('暂无数据');
          }
          this.isLoading=false;
        });
      },
      /*指定学生到班*/
      assginStudentAjax(){
        if(this.ajaxType=='assign'){
          /*指定学生到班*/
          changeBySelfAssign({gradeId:this.gradeId,ids:this.ids,proClass:{classId:this.classId,class:this.className}}).then(data=>{
            if(data.status){
              this.vmMsgSuccess('加入班级成功！');
              this.ids=[];
              this.preClass=[];
              this.getLoadAjax();
              this.getPersonAjax();
            }
            else{
              this.vmMsgError('加入班级失败，请重试！');
            }
          });
        }
        else if(this.ajaxType=='equal'){
          /*相邻分数互换*/
          changeBySelfEqual({gradeId:this.gradeId,ids:this.ids,preClass:this.preClass,proClass:{classId:this.classId,class:this.className}}).then(data=>{
            if(data.status){
              this.vmMsgSuccess('加入班级成功！');
              this.ids=[];
              this.preClass=[];
              this.getLoadAjax();
              this.getPersonAjax();
            }
            else{
              this.vmMsgError(data.msg);
            }
          });
        }
      }
    },
    created(){
      this.gradeId=this.$route.params.gradeId;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
  .g-importCourse .g-container .g-section{margin:1.25rem 0;width:100%;}
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
  .g-textHeader{
    h2{.marginLeft(40,1582);}
  }
  .g-chooseButton div{

  }
  /*调整方式处css*/
  .g-chooseButton{/*1582*/
    width:100%;padding:30/16rem 0 0;.fontSize(14);color:@normalColor;
    span{.marginRight(20,1582);}
    div{padding:8/16rem 1rem;
      &:hover{cursor:pointer;}
    }
    div:first-of-type{.border-bottom-left-radius(4/16rem);.border-top-left-radius(4/16rem);}
    div:last-of-type{.border-bottom-right-radius(4/16rem);.border-top-right-radius(4/16rem);}
    div.activeCss{background:@green;color:#fff;border:none;}
    div.normalCss{color:@normalColor;border:1px solid @borderColor;}
  }
  /*下方css*/
  .g-section{width:100%;.marginTop(20);
    .g-joinClassPart{/*640*/
      .width(640,1582);
      .g-textHeader{.fontSize(14);color:@normalColor;.height(40);}
      .g-joinClassContainer{
        width:100%;
        li{.width(150,640);.NotLineheight(160);float:left;.marginBottom(30);position:relative;
          &>div{width:100%;height:100%;
            h2{.fontSize(20);text-align:center;color:@HColor;padding:20/16rem 0;
              span:not(:first-of-type){}
            }
            p{color:@normalColor;.fontSize(15);width:100%;text-align:center;}
          }
          .radiusButton.el-button{padding:0.5rem 20/16rem;left:50%;.transformTranslate(-50%,50%);}
        }
        //li:not(:first-of-type){.marginLeft(20,640);}
      }
    }
    .activeBoxShadow{.box-shadow(0 0 10/16rem 1/16rem rgba(0,0,0,.2));border:2/16rem solid @backgroundBlue;}
    .normalBoxShadow{border:2/16rem solid @borderColor;}
    div[class*='BoxShadow']:hover{cursor:pointer;}
  }
</style>








