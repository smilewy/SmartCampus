<template>
  <div class="g-ManualScheduce">
    <header class="g-timeHeader">
      <div class="g-buttonGroup">
        <el-button class="g-gobackChart RedButton" @click="goBackChart">
          <img src="../../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png"/>
          返回流程图
        </el-button>
        <!--<el-button class="blueButton" @click="saveSetting">保存</el-button>-->
      </div>
    </header>
    <section class="g-classesTimeSetSection">
      <div class="g-sectionL">
        <header class="gL-header">
          <h2>待选老师</h2>
          <el-input @input="fuzzyClick" v-model="fuzzyInput" class="fuzzyInput" placeholder="请输入教师姓名"
                    suffix-icon="el-icon-search"></el-input>
        </header>
        <section class="gL-section">
          <el-tree
                v-loading="loadingTree"
                element-loading-text="拼命加载中"
                element-loading-spinner="el-icon-loading"
                :highlight-current="true"
                :data="treeData"
                :props="defaultProps"
                ref="allMsg"
                :filter-node-method="filterNode"
                @node-click="handleNodeClick">
          </el-tree>
        </section>
      </div>
      <div class="g-sectionR alertsList">
        <header>
          <h2 v-if="teacherHeaderText" v-text="teacherHeaderText+'课表'"></h2>
          <h2 v-else>(教师课表)课表</h2>
        </header>
        <el-table
            v-loading="loadingTable1"
            element-loading-text="拼命加载中"
            element-loading-spinner="el-icon-loading"
            :data="teacherTimeSetTable"
            border
            class="g-timeSettingTable g-ManualScheduceTable">
          <el-table-column label="节/周">
            <template slot-scope="props">
              <!-- @cell-click="tableCellClick"-->
              <span v-text="'第'+(props.$index+1)+'节'"></span>
            </template>
          </el-table-column>
          <el-table-column v-for="n in 7" :key="n" :label="weekData[n-1]">
            <template slot-scope="props">
              <span v-if="props.row[n-1].statu==0" class="NotCourse">不上课</span>
              <span v-if="props.row[n-1].statu==2 || props.row[n-1].statu==3 || props.row[n-1].statu==4"
                    class="NotCourse">不排课</span>
              <span v-if="props.row[n-1].statu==5" class="NotCourse">
                {{props.row[n-1].gradeName}}
                {{props.row[n-1].className}}
              </span>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <div class="g-sectionR alertsList">
        <header>
          <!--          <h2 v-if="classHeaderText" v-text="classHeaderText+'课表'"></h2>
                    <h2 v-else>(班级课表)课表</h2>-->
          <el-form ref="selectGroup" :model="selectGroupForm" class="moreFormItem g-selectGroup">
            <el-form-item prop="gradeId" style="width:20%;">
              <el-select v-model="selectGroupForm.gradeId" @change="chooseGrade">
                <el-option v-for="(content,index) in gradeArray" :key="index" :label="gradeData[content.gradeName-1]"
                           :value="content.gradeId"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item prop="classesId" style="width:20%;">
              <el-select v-model="selectGroupForm.classesId" @change="chooseClass">
                <el-option v-for="(content,index) in classArray" :key="index" :label="content.className+'班'"
                           :value="content.classId"></el-option>
              </el-select>
            </el-form-item>
          </el-form>
        </header>
        <el-table
              v-loading="loadingTable2"
              element-loading-text="拼命加载中"
              element-loading-spinner="el-icon-loading"
              :data="classesTimeSetTable"
              @cell-click="classTableCellClick"
              border
              class="g-timeSettingTable g-ManualScheduceTable">
          <el-table-column label="节/周">
            <template slot-scope="props">
              <span v-text="'第'+(props.$index+1)+'节'"></span>
            </template>
          </el-table-column>
          <el-table-column v-for="n in 7" :key="n" :label="weekData[n-1]">
            <template slot-scope="props">
              <span v-if="props.row[n-1].statu==0" class="NotCourse">不上课</span>
              <span v-if="props.row[n-1].statu==2 || props.row[n-1].statu==3 || props.row[n-1].statu==4"
                    class="NotCourse">不排课</span>
              <span v-if="props.row[n-1].statu==5" class="SetNotCourse" v-text="props.row[n-1].subjectName + '(' + props.row[n-1].subjectName + ')'"></span>
              <span v-if="props.row[n-1].statu==6" class="SetChangeCourse" v-text="props.row[n-1].subjectName + '(' + props.row[n-1].subjectName + ')'"></span>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {
    ManualScheduceGetTeacher,//得到教师
    ManualScheduceGetClass,//得到年级
    ManualScheduceTeacherCourse,//得到教师课程表
    ManualScheduceClassCourse,//得到班级课程表
    ManualScheduceChangeCourse,//调课接口
  } from '@/api/http'
  import {fuzzyQuery} from '@/assets/js/fuzzyQuery'

  export default {
    data() {
      return {
        pkListId: '',
        /*教师课表表格header文本*/
        teacherHeaderText: '',
        teacherTimeSetTable: [],
        /*班级课表header文本*/
        classHeaderText: '',
        classesTimeSetTable: [],
        /*select选择年级数据双向绑定*/
        selectGroupForm: {
          gradeId: '',
          classesId: ''
        },
        /*教师模糊查询*/
        fuzzyInput: '',
        /*tree*/
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'techerName',
        },
        /*table数据请求所需参数*/
        techerId: '',
        subjectId: '',
        techerName: '',
        /*年级array*/
        gradeArray: [],
        /*班级array*/
        classArray: [],
        /*年级显示转换*/
        gradeData: ['一年级', '二年级', '三年级', '四年级', '五年级', '六年级', '初一', '初二',
          '初三', '高一', '高二', '高三'
        ],
        /*星期转换*/
        weekData: ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'],
        loadingTable2: false,
        loadingTable1: false,
        loadingTree: false
      }
    },
    methods: {
      /*点击返回流程图按钮*/
      goBackChart() {
        this.$router.push({name: 'examinationChart'});
      },
      /*保存*/
      saveSetting() {
        teacherTimeSettingSaved({
          pkListId: this.pkListId,
          teacherId: this.techerId,
          teacherName: this.techerName,
          data: this.classesTimeSetTable
        }).then(data => {
          if (data.statu == 1) {
            this.vmMsgSuccess('保存成功！');
            this.getTableData();
          } else {
            this.vmMsgError('保存失败！');
          }
        })
      },
      /*班级课表table*/
      /*选择年级*/
      chooseGrade() {
        /*获取班级*/
        this.sendClassAjax();
        /*清空班级选择*/
        this.selectGroupForm.classesId = '';
      },
      /*选择年级*/
      chooseClass() {
        /*获取班级课表table信息*/
        this.sendClassTableAjax();
      },
      /*教师信息模糊查询*/
      fuzzyClick() {
        /*input框输入值改变触发的函数*/
        this.$refs['allMsg'].filter(this.fuzzyInput);
      },
      filterNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.techerName.indexOf(value) !== -1;
      },
      /*tree点击事件*/
      handleNodeClick(data, node) {
        /*点击节点回调*/
        /*点击名字发送请求，返回数据绑定在右边部分*/
        /*给每一级赋值一个唯一的id即可辨认点击的是几级*/
        if ('data' in data) {
          return false;
        } else {
          this.techerId = data.techerId;
          this.subjectId = data.subjectId;
          this.techerName = data.techerName;
          this.teacherHeaderText = data.techerName + '(' + node.parent.data.techerName + ')';
          this.getTeacherTableData();
          /*清空班级课程表*/
          this.$refs['selectGroup'].resetFields();
          this.classesTimeSetTable = [];
        }
      },
      /*table框点击单元格事件*/
      classTableCellClick(row, column, cell, event) {
        const columnNum = this.weekData.indexOf(column.label);
        if (columnNum >= 0) {
          if (row[columnNum].statu == 0 || row[columnNum].statu == 2 || row[columnNum].statu == 3 || row[columnNum].statu == 4) {
            return false;
          }
          else {
            let changeCount;
            if (row[columnNum].statu == 5) {
              row[columnNum].statu = 6;
              changeCount = this.checkUsedCount();
              if (changeCount < 2) {
                return false;
              }
              else if (changeCount == 2) {
                /*发送请求*/
                this.changeCourseAjax();
              }
              else {
                this.vmMsgWarning('正在发起调课请求，请稍后！');
              }
            }
          }
        }
      },
      /*检测点击了几个有效单元格*/
      checkUsedCount() {
        let count = 0;
        this.classesTimeSetTable.forEach((row, rowI) => {
          row.forEach((column, columnI) => {
            if (column.statu == 6) {
              count++;
            }
          });
        });
        return count;
      },
      /*send ajax*/
      /*得到班级*/
      sendClassAjax() {
        ManualScheduceGetClass({
          pkListId: this.pkListId,
          techerId: this.techerId,
          gradeId: this.selectGroupForm.gradeId
        }).then(data => {
          if (data.statu) {
            this.classArray = data.data;
          }
          else {
            this.vmMsgError('班级加载失败，请重新选择年级！');
          }
        })
      },
      /*得到班级课表信息*/
      sendClassTableAjax() {
        this.loadingTable2 = true;
        ManualScheduceClassCourse({
          pkListId: this.pkListId,
          classId: this.selectGroupForm.classesId,
          gradeId: this.selectGroupForm.gradeId
        }).then(data => {
          this.loadingTable2 = false;
          if (data.statu) {
            this.gradeArray = data.gradeAndClass;
            this.classesTimeSetTable = data.data;
          } else {
            this.vmMsgError('班级课程表加载失败！');
          }
        });
      },
      /*班级课表调课*/
      changeCourseAjax() {
        ManualScheduceChangeCourse({
          techerId: this.techerId,
          subjectId: this.subjectId,
          pkListId: this.pkListId,
          classId: this.selectGroupForm.classesId,
          gradeId: this.selectGroupForm.gradeId,
          data: this.classesTimeSetTable
        }).then(data => {
          this.sendClassTableAjax();
          if (data.statu == 1) {
            this.vmMsgSuccess('调课成功！');
            this.getTeacherTableData();
          }
          else {
            this.vmMsgError(data.message);
          }
        });
      },
      /*得到教师ajax*/
      getTeacherAjax() {
        this.loadingTree = true;
        ManualScheduceGetTeacher({pkListId: this.pkListId}).then(data => {
          this.loadingTree = false;
          if (data.statu) {
            this.treeData = data.data;
          } else {
            this.vmMsgError('加载失败,请重新加载页面!');
          }
        })
      },
      /*得到表格默认信息*/
      getTeacherTableData() {
        this.loadingTable1 = true;
        ManualScheduceTeacherCourse({pkListId: this.pkListId, techerId: this.techerId}).then(data => {
          this.loadingTable1 = false;
          if (data.statu) {
            this.gradeArray = data.gradeAndClass;
            this.teacherTimeSetTable = data.data;
          } else {
            this.vmMsgError('教师课程表加载失败！');
          }
        })
      },
    },
    created() {
      this.pkListId = sessionStorage.pkListId;
      this.getTeacherAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/arrangeClasses/ManualScheduce';
  @import '../../../../style/arrangeClasses/arrangeClasses.css';

  .moreFormItem {
    padding: 8/16rem;
    .box-sizing();
  }
</style>




