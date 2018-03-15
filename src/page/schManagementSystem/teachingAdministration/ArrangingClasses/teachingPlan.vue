<template>
  <div class="g-teachingPlan">
    <header class="g-teachingPlanHeader">
      <el-button class="g-gobackChart RedButton" @click="goBackChart">
        <img src="../../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
        返回流程图
      </el-button>
      <el-button class="blueButton" :disabled="!canEdit" @click="saveSetting">保存</el-button>
    </header>
    <section class="g-teachingPlanSection g-headerButtonCss">
      <div class="g-teachingHeaderH">
        <h2>教学计划表</h2>
        <span style="color:#ff5555">* 教师姓名不可修改，可在教学管理->任课教师模块下添加（每位教师、每个班级每周安排的课时数不能超过本周的总课时数）</span>
      </div>
      <div class="gs-button alertsBtn">
        <el-button-group class="elGroupButton_two">
          <el-button @click="buttonClick" data-msg="copy" class="filt" title="复制">
            <img class="filt_unactive" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
            <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
          </el-button>
          <el-button @click="buttonClick" data-msg="print" class="filt buttonChild" title="打印预览">
            <img class="filt_unactive" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
            <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
          </el-button>
        </el-button-group>
      </div>
      <section class="g-teachingPlanContent alertsList">
        <el-table class="g-hasBorder g-NotHover"
                  v-loading="loading"
                  element-loading-text="拼命加载中"
                  element-loading-spinner="el-icon-loading"
                  :data="teachingPlanTable"
                  height="500"
                  border>
          <el-table-column width="200px" label="班级" fixed>
            <template slot-scope="props">
              <span v-text="gradeData[props.row.gradeName-1]+props.row.className+'班'"></span>
            </template>
          </el-table-column>
          <el-table-column width="230px" v-for="(content,n) in teachingPlanColumn" :label="content.subjectname" :key="n">
            <template slot-scope="props">
              <span class="g-teacherName" v-text="props.row.data[n].techerName" :data-index="props.row.data[n].techerId"></span>
              <!-- <el-input-number :disabled="!props.row.data[n].techerName" @change="changeMaxPage(props.$index,n)" class="g-courseNum" v-model="props.row.data[n].count" :min="0" :max="maxPageArr[props.$index][n]"></el-input-number> -->
              <el-input-number size="mini" :disabled="!props.row.data[n].techerName || !canEdit" @change="changeMaxPage(props.row.data[n].classId, props.row.data[n].techerId, props.$index, n)" class="g-courseNum" v-model="props.row.data[n].count" :min="0" :max="maxPageArr[props.$index][n]"></el-input-number>
            </template>
          </el-table-column>
        </el-table>
      </section>
    </section>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {teachingPlanLoad,
    teachingPlanCreate,//保存
  } from '@/api/http'
  export default{
    data(){
      return {
        pkListId: '',
        /*table数据绑定*/
        /*行数据*/
        teachingPlanTable:[],
        /*列数据*/
        teachingPlanColumn:[],
        /*年级显示转换*/
        gradeData:['一年级','二年级','三年级','四年级','五年级','六年级','初一','初二',
          '初三','高一','高二','高三'
        ],
        /*最大值*/
        maxPageArr:[],
        maxNum:0,
        /*记数框变化的位置*/
        numRow:0,
        numColumn:0,
        loading: false,

        // 判断是否能修改教学计划
        canEdit: true
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'examinationChart'});
      },
      /*修改计数器最大值*/
      changeMaxPage(index,columnI){
        this.numRow = index;
        this.numColumn = columnI;
      },
      changeMaxPage( classId, teacherId, rowIndex, columnIndex ){
        // 取得对应班级
        let classData = this.teachingPlanTable.find( o => o.classId == classId );
        // 计算该班级已安排课时
        let arrangedNumbers = classData.data.reduce( (num, tempObj) => { return num + Number(tempObj.count); }, 0);

        // 取得当前教师
        let teacher = classData.data.find( o => o.techerId == teacherId );
        let preVal = teacher.count;
        classData.data.forEach( (obj, index) => {
          this.$nextTick( () => {
            if( index != columnIndex ){
              let rows = this.maxPageArr[rowIndex];
              rows[index] = this.maxNum - arrangedNumbers + obj.count + (teacher.count - preVal > 0 ? -1 : 1);
              this.maxPageArr.splice(rowIndex, 1, rows);
            }
          });
        });


        // 取得对应教师在所教的所有班级已安排课时
        let teacherNumbers = this.teachingPlanTable.map( obj => {
          let teacher = obj.data.find( o => o.techerId == teacherId );
          return teacher ? Number(teacher.count) : 0;
        }).reduce( (num, value) => num + value, 0 );


        // 对某个老师多个班级排课时的处理 -- 该老师在多个班级的排课总数不超过一周课时数
        this.teachingPlanTable.forEach( (obj, index) => {

          let temp = obj.data.find( o => o.classId != classId && o.techerId == teacherId );
          if( temp ){
            let colIndex = obj.data.indexOf( temp );
            this.$nextTick( () => {
              let val = this.maxNum - (teacher.count - preVal > 0 ? teacherNumbers : teacherNumbers - 1) + temp.count ;
              let rows = this.maxPageArr[index];
              rows[colIndex] = Math.min( this.maxNum - ((teacher.count - preVal > 0 ? teacherNumbers + 1: teacherNumbers - 1) - temp.count), val + (teacher.count - preVal > 0 ? -1 : 0));
              this.maxPageArr.splice(index, 1, rows);
            });
          }
        })



        if( arrangedNumbers >= this.maxNum || teacherNumbers >= this.maxNum ){
          this.maxPageArr[rowIndex][columnIndex] = teacher.count;

          let preVal = teacher.count;
          this.$nextTick( () => {
            if( arrangedNumbers >= this.maxNum && teacher.count > preVal ){
              this.vmMsgWarning('无法增加，本班已安排课时数已超过总的可安排课时数！');
            }
            if( teacherNumbers >= this.maxNum && teacher.count > preVal ){
              this.vmMsgWarning('无法增加，该教师已安排课时数已超过总的可安排课时数！');
            }
          });
        }
      },

      /*保存*/
      saveSetting(){
        teachingPlanCreate({pkListId:this.pkListId,data:this.teachingPlanTable}).then(data=>{
          if(data.statu){
            this.vmMsgSuccess('保存成功！');
          }else{
            this.vmMsgError('保存失败！');
          }
        })
      },
      /*header的button群*/
      buttonClick(event){
        const e=$(event.currentTarget),targetMsg=e.data('msg');
        if(targetMsg=='copy'){
//          this.isDialog=true;
        }
      },
      /*sendAjax*/
      loadingAjax(){
        this.loading = true;
        teachingPlanLoad({pkListId:this.pkListId}).then(data=>{
          this.loading = false;
          this.teachingPlanColumn = data.subject; // 科目数据
          if(data.statu){
            this.canEdit = data.check == 1 ? false : true;
            this.teachingPlanTable = data.data;  // 各年级各班级各科目的排课数据
            this.maxNum = data.allCount; // 一周上课总课时
            for(let i = 0, len = data.data.length; i < len; i++){
              this.maxPageArr.push([]);
              // 某个年纪某个班级各科目老师的排课情况
              let subjectData = data.data[i].data;
              // 某个班级已排课节数
              let arrangedNumbers = subjectData.reduce( (num, tempObj) => { return num + Number(tempObj.count); }, 0);
              for(let j = 0, length = subjectData.length; j < length; j++){
                // 某教师可排课节数 = 一周课时总节数 - 该教师已排课节数 与 一周课时总节数 - 该班级已排课节数 -> 两者中较小值
                let number = Math.min( Number(data.allCount) - Number(subjectData[j].count), Number(data.allCount) - arrangedNumbers );
                this.maxPageArr[i].push( number <= 0 ? Number(subjectData[j].count) : Number(subjectData[j].count) + number );
              }
            }
          }
          else{
            this.teachingPlanTable=[];
            this.teachingPlanColumn=[];
            this.vmMsgError('数据加载失败!');
          }
        })
      },
    },
    created(){
      this.pkListId = sessionStorage.pkListId;
      this.loadingAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/arrangeClasses/arrangeClasses.css';
  @import '../../../../style/arrangeClasses/teachingPlan.less';
</style>



