<template>
  <div class="g-container">
    <header class="g-header">
      <div class="gh-header">排课管理</div>
    </header>
    <section class="g-section">
      <div class="gs-header">
        <div class="gs-button alertsBtn">
          <el-button type="primary" @click="buttonClick" data-msg="add" class="el-icon-plus g-imgContainer">新增排课方案
          </el-button>
        </div>
      </div>
      <div class="gs-table alertsList">
        <el-table class="g-NotHover"
                  ref="studentMsgTable"
                  v-loading="loading"
                  element-loading-text="拼命加载中"
                  element-loading-spinner="el-icon-loading"
                  :data="headerButtonData.LoadBasicMsg"
                  style="width:100%">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <el-table-column label="序号" type="index" width="150"></el-table-column>
          <el-table-column label="方案名称" sortable prop="pkPlanName">
            <template slot-scope="props">
              <a href="javascript:void(0);" :data-msg="props.row.id" @click="changeExamChart"
                 v-text="props.row.pkPlanName"></a>
            </template>
          </el-table-column>
          <el-table-column label="排课范围">
            <template slot-scope="props">
              <span v-for="range in props.row.name.split(',')" v-text="gradeData[range-1]"></span>
            </template>
          </el-table-column>
          <el-table-column label="启用时间">
            <template slot-scope="scope">
              <span>{{scope.row.startTime}} 至 {{scope.row.endTime}}</span>
            </template>
          </el-table-column>
          <el-table-column label="是否启用">
            <template slot-scope="props">
              <div v-if="Number(props.row.ifStartUp)">已发布</div>
              <div v-else>初始化</div>
            </template>
          </el-table-column>
          <el-table-column label="操作">
            <template slot-scope="scope">
              <el-button type="text" @click="copeCreate(scope.$index)">复制</el-button>
              <el-button class="deleteColor" type="text" @click="deleteClick(scope.$index)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
    <el-dialog class="addDialog" :title="dialogTitle" :modal="false" :visible.sync="isDialog">
      <el-form ref="addDataForm" :rules="addRule" :model="addForm" label-width="90px">
        <el-form-item label="排课名称:" prop="pkPlanName">
          <el-input v-model="addForm.pkPlanName"></el-input>
        </el-form-item>
        <el-form-item label="排课范围:" prop="pkRange">
          <el-select multiple placeholder="请选择排课范围" v-model="addForm.pkRange">
            <el-option v-for="(gradeMsg,i) in  headerButtonData.gradeAjaxData" :key="i"
                       :label="gradeMsg.znName" :value="gradeMsg.gradeid"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="起始日期:" prop="startTime">
          <el-date-picker :editable="false" :picker-options="pickerOptionsStart" placeholder="选择起始日期" v-model="addForm.startTime"
                          style="width:100%"></el-date-picker>
        </el-form-item>
        <el-form-item label="结束日期:" prop="endTime">
          <el-date-picker :editable="false" :picker-options="pickerOptionsEnd" placeholder="选择结束日期" v-model="addForm.endTime"
                          style="width:100%"></el-date-picker>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click="addCourseClick">保存</el-button>
      </div>
    </el-dialog>
    <el-dialog class="copyDialog" title="复制排课方案" :modal="false" :visible.sync="isCopyDialog">
      <el-form ref="copyForm" :model="copyForm" label-width="90px">
        <el-form-item label="排课名称:">
          <el-input v-model="copyForm.pkPlanName"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click="copyCourseSave">保存</el-button>
      </div>
    </el-dialog>
    <!--提示框-->
  </div>
</template>
<script>
  import {mapMutations} from 'vuex'
  import {
    arrangeManageLoad,//页面加载数据
    arrangeManageAdd,//添加排课方案
    arrangeManageDelete,//删除排课方案
    arrangeManageGradeRange,//得到年级范围
  } from '@/api/http'
  export default{
    data(){
      return {
        /*ajax data*/
        headerButtonData: {
          gradeloadData: [],
          classesLoadData: [],
          msgTypeLoadData: [],
          LoadBasicMsg: [],//加载页面的table数据
          gradeAjaxData: [],//添加弹框排课范围
        },
        /*年级显示转换*/
        gradeData: ['一年级', '二年级', '三年级', '四年级', '五年级', '六年级', '初一', '初二',
          '初三', '高一', '高二', '高三'
        ],
        /*switch数据双向绑定,当发送请求时需要转换值，switch为true时，headerButtonData.LoadBasicMsg.ifStartUp=3；false为2*/
        /*弹框*/
        dialogTitle: '新增排课方案',
        isDialog: false,
        addForm: {
          pkPlanName: '',
          pkRange: [],
          startTime: '',
          endTime: '',
        },
        /*复制弹框*/
        isCopyDialog: false,
        copyIndex: '',
        copyForm: {
          pkPlanName: '',
        },
        /*rule*/
        addRule: {
          pkPlanName: [{required: true, message: '请填写排课名称'}],
          pkRange: [{required: true, message: '请填写排课范围'}],
          startTime: [{required: true, message: '请选择起始日期'}],
          endTime: [{required: true, message: '请选择结束日期'}],
        },
        /*时间选择限制*/
        pickerOptionsStart: {
          disabledDate: (time) => {
            if (this.addForm.endTime) {
              return time.getTime() > this.addForm.endTime;
            }
            return time.getTime() < Date.now() - 8.64e7;
          }
        },
        pickerOptionsEnd: {
          disabledDate: (time) => {
            if (this.addForm.startTime) {
              return time.getTime() < this.addForm.startTime;
            }
            return time.getTime() < Date.now() - 8.64e7;
          }
        },
        loading: false
      }
    },
    computed: {},
    methods: {
      /*修改教学方案页面信息*/
      changeExamChart(event){
        const e = $(event.currentTarget);
        sessionStorage["theArrangeClasses"] = e.text();
        sessionStorage["pkListId"] = e.data('msg');
        this.$router.push({name: 'examinationChart'});
      },
      /*复制*/
      copeCreate(index){
        this.isCopyDialog = true;
        this.copyIndex = index;
      },
      /*删除*/
      deleteClick(index){
        this.vmConfirm({
          msg: '确定删除此条排课方案？',
          confirmCallback: () => {
            arrangeManageDelete({ id: this.headerButtonData.LoadBasicMsg[index].id }).then( data => {
              if (data.statu) {
                this.vmMsgSuccess( '删除成功！' );
                this.sendLoadAjax();
              } else {
                this.vmMsgError( '删除失败，请重试！' );
              }
            });
          }
        });
      },
      /*点击header处的button*/
      buttonClick(event){
        const e = $(event.currentTarget), targetMsg = e.data('msg');
        if (targetMsg == 'add') {
          this.isDialog = true;
          this.dialogTitle = '新增排课方案';
          if (this.$refs['addDataForm']) {
            this.$refs['addDataForm'].resetFields();
          }
          this.getGradeRangeAjax();
        }
      },
      /*新增排课方案*/
      addCourseClick(){
        this.$refs['addDataForm'].validate((valid) => {
          if (valid) {
            if (new Date(this.addForm.startTime).getTime() >= new Date(this.addForm.endTime).getTime()) {
              this.vmMsgWarning( '起始日期和结束日期不能是同一天！' ); return false;
            }
            let loadingIns = this.vmLoadingFull( '处理中，请稍后...' );
            arrangeManageAdd({
              pkPlanName: this.addForm.pkPlanName,
              pkRange: this.addForm.pkRange.join(','),
              startTime: this.addForm.startTime,
              endTime: this.addForm.endTime
            }).then(data => {
              if (data.statu) {
                this.isDialog = false;
                this.vmMsgSuccess( '添加成功！' );
                this.sendLoadAjax();
              } else {
                this.vmMsgError( '添加失败，请重试！' );
              }
              loadingIns.close();
            });
          } else {
            this.vmMsgWarning( '请填完整相关信息!' );
          }
        });
      },
      /*send ajax*/
      /*复制信息保存*/
      copyCourseSave(){
        if (this.copyForm.pkPlanName) {

          /*判断是否重名*/
          let isExist = this.headerButtonData.LoadBasicMsg.some( o => o.pkPlanName == this.copyForm.pkPlanName );
          if( isExist ) { this.vmMsgError( '已存在该方案名，请重新输入！' ); return; } 

          arrangeManageAdd({
            id: this.headerButtonData.LoadBasicMsg[this.copyIndex].id,
            pkPlanName: this.copyForm.pkPlanName,
            pkRange: this.headerButtonData.LoadBasicMsg[this.copyIndex].pkRange,
            startTime: this.headerButtonData.LoadBasicMsg[this.copyIndex].startTime,
            endTime: this.headerButtonData.LoadBasicMsg[this.copyIndex].endTime
          }).then( data => {
            this.isCopyDialog = false;
            if (data.statu) {
              this.vmMsgSuccess( '复制成功！' );
              this.sendLoadAjax();
            } else {
              this.vmMsgError( '复制失败，请重试！' );
            }
          });
        }
        else {
          this.vmMsgError( '请输入方案名！' );
        }
      },
      /*得到排课方案*/
      sendLoadAjax(){
        this.loading = true;
        arrangeManageLoad().then( data => {
          this.loading = false;
          this.headerButtonData.LoadBasicMsg = this.handlerData(data);
        });
      },
      /*得到年级范围*/
      getGradeRangeAjax(){
        arrangeManageGradeRange().then(data => {
          this.headerButtonData.gradeAjaxData = data;
        })
      },
      /*处理数据*/
      handlerData(data){
        if (data.statu) {
          return data.data;
        } else {
          this.vmMsgError( '加载失败,请重新加载页面！' );
        }
      },
    },
    created(){
      this.sendLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/arrangeClasses/arrageClasses.less';
  @import '../../../../style/arrangeClasses/arrangeClasses.css';
</style>















