<template>
  <div class="g-container">
    <header class="g-header">
      <div class="gh-section clear_fix">
        <el-form ref="totalScheduleForm" lable-position="left" :model="dataHeader" label-width="85px">
          <el-form-item label="显示教师:">
            <el-switch active-text="是" inactive-text="否" active-color="#13b5b1" inactive-color="#999"
                       v-model="dataHeader.showTeacher"></el-switch>
          </el-form-item>
          <!--          <el-form-item label="科目单字:">
                      <el-switch active-text="是" inactive-text="否" active-color="#13b5b1" inactive-color="#999" v-model="dataHeader.subjectOneWord"></el-switch>
                    </el-form-item>-->
        </el-form>
      </div>
    </header>
    <section class="g-section">
      <div class="gs-header">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="exportClick" class="filt" title="导出">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"/>
            </el-button>
          </el-button-group>
          <el-button-group class="elGroupButton_two">
            <el-button class="filt" title="复制">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"/>
            </el-button>
            <el-button class="filt" title="打印预览">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"/>
            </el-button>
          </el-button-group>
        </div>
      </div>
      <div class="gs-table alertsList">
        <el-table
              v-loading="loadingTable"
              element-loading-text="拼命加载中"
              element-loading-spinner="el-icon-loading"
              :data="classesTimeSetTable"
              border
              class="g-timeSettingTable NotTdPadding">
          <el-table-column label="班级" prop="name"></el-table-column>
          <el-table-column v-for="n in 8" :key="n" :label="weekData[n-1]">
            <template slot-scope="prop">
              <div class="table-cell" v-for="(content,columnI) in prop.row.data[n-1]">
                <!--第一列只有节次-->
                <span class="NotHeight" v-if="n==1" v-text="content.subjectName"></span>
                <div class="g-tabelDivContent" v-else>
                  <h2 v-text="content.subjectName"></h2>
                  <span class="NotHeight" v-show="dataHeader.showTeacher && content.teacherName"
                        v-text="'('+content.teacherName+')'"></span>
                </div>
              </div>
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
    totalScheduleGetCourse, /*table默认数据*/
  } from '@/api/http'
  import req from '@/assets/js/common'

  export default {
    data() {
      let _self = this;
      return {
        pkListId: '',
        /*form表单双向绑定数据*/
        dataHeader: {
          /*显示div中文件信息值*/
          showTeacher: '',
          subjectOneWord: '',
        },
        /*table数据请求所需参数*/
        gradeId: '',
        gradeName: '',
        classId: '',
        className: '',
        /*table框绑定数据*/
        classesTimeSetTable: [],
        testTable: [{name: '1', value: [1, 2, 3]}, {name: '2', value: _self.classesTimeSetTable}],
        /*星期转换*/
        weekData: ['节/周', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'],
        loadingTable: false
      }
    },
    methods: {
      /*table框点击单元格事件*/
      tableCellClick(row, column, cell, event) {
        const columnNum = this.weekData.indexOf(column.label);
        if (columnNum >= 0) {
          if (row[columnNum] == 0) {
            this.vmMsgWarning( '已设置不上课，不能限制!' );
          } else {
            if (row[columnNum] == 2) {
              this.$set(row, columnNum, 1);
            } else {
              this.$set(row, columnNum, 2);
            }
          }
        }
      },
      /*send ajax*/
      /*导出课表*/
      exportClick() {
        req.downloadFile('.g-classSchedule', '/school/curriculum/table?type=getAllClassTableExport&pkListId=' + this.pkListId, 'post');
      },
      /*得到表格默认信息*/
      getTableData() {
        this.loadingTable = true;
        totalScheduleGetCourse({pkListId: this.pkListId}).then(data => {
          this.loadingTable = false;
          if (data.statu == 1) {
            this.classesTimeSetTable = data.data;
          } else {
            this.vmMsgError( '数据加载失败，请重试！' );
          }
        })
      }
    },
    created() {
      this.pkListId = sessionStorage.pkListId;
      this.getTableData();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/arrangeClasses/classSchedule/totalSchedule';
  @import '../../../../../style/arrangeClasses/arrangeClasses.css';
</style>


