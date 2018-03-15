<template>
  <div class="g-container roleInformation">
    <header class="g-header">
      <div class="gh-header">角色信息管理</div>
      <div class="gh-section g-formNoMarB g-flexStartRow">
        <el-form ref="studentMessge" label-position="left" :model="dataHeader" label-width="80px">
          <el-form-item label="用户类型:" prop="gradeIdValue">
            <el-select class="g-select" v-model="dataHeader.gradeIdValue" placeholder="请选择年级">
              <el-option v-for="(content,index) in headerButtonData.gradeloadData" :value="content.nameId"
                         :label="content.name" :key="index"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <el-button type="primary" class="g-buttonSearch el-icon-search" @click="searchClick">查询</el-button>
      </div>
      <!--      <div class="gh-buttonGroup clear_fix">
              <el-button icon="reset" @click="resetClick">重置</el-button>

            </div>-->
    </header>
    <section class="g-section">
      <div class="gs-header">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="buttonClick" data-msg="export" class="filt" title="导出">
              <img class="filt_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"/>
              <img class="filt_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"/>
            </el-button>
          </el-button-group>
          <el-button-group class="elGroupButton_two">
            <el-button data-msg="copy" class="filt" title="复制" @click="operationData('copy')">
              <img class="filt_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"/>
              <img class="filt_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"/>
            </el-button>
            <el-button data-msg="print" class="filt" title="打印" @click="operationData('print')">
              <img class="filt_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"/>
              <img class="filt_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"/>
            </el-button>
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" placeholder="用户名/名字/手机" suffix-icon="el-icon-search"
                    @change="fuzzyClick"></el-input>
        </div>
      </div>
      <div class="gs-table alertsList">
        <el-table class="g-NotHover" v-if="tableColumnData.length>0" ref="studentMsgTable"
                  :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange"
                  @selection-change="handleStudentTable"
                  v-loading="loading"
                  element-loading-text="拼命加载中">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <el-table-column v-for="(content,index) in tableColumnData" :key="index" :label="content.zh"
                           :prop="content.en" sortable="custom"></el-table-column>
          <el-table-column label="操作">
            <template slot-scope="prop">
              <el-button class="greenButtonColor" @click="updatePwdAjax(prop.row.id)" type="text">重置密码</el-button>
              <el-button type="text" v-if="Number(prop.row.state)" @click="disabledPwdAjax(prop.row.id,'停用')">停用
              </el-button>
              <el-button class="deleteColor" @click="disabledPwdAjax(prop.row.id,'启用')" v-else type="text">启用
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
    <footer class="g-footer">
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          layout="prev, pager, next, jumper"
          :page-count="pageAll"
        >
        </el-pagination>
      </el-row>
    </footer>
  </div>
</template>
<script>
  import {
    roleInformationUser,//得到用户类型
    roleInformationLoad,//得到表格加载数据
    roleInformationUpdataPwd,//重置密码
    roleInformationDisabled,//禁用密码
  } from '@/api/http'
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        /*ajax data*/
        headerButtonData: {
          gradeloadData: [],//得到年级返回数据
          classesLoadData: [],
          msgTypeLoadData: [],
          studentBasicMsg: [],//table数据返回
        },
        tableColumnData: [],
        /*form表单双向绑定数据*/
        dataHeader: {
          /*select*/
          gradeIdValue: ''
        },
        /*table*/
        isFilter: false,
        /*发送请求排序参数*/
        orderBy: '',//排序的字段
        sort: '',//排序方式
        /*fuzzyFilter*/
        fuzzyInput: '',
        /*footer*/
        pageAll: 1,
        currentSize: 10,
        currentPage: 1,
        loading:false
      }
    },
    computed: {},
    methods: {
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.sendLoadAjax();
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
      },
      sortChange(column){
        /*table排序回调*/
        this.sort = column.order;
        this.orderBy = column.prop;
        this.sendLoadAjax();
      },
      /*mine*/
      buttonClick(event){
        const e = $(event.currentTarget), targetMsg = e.data('msg');
        if (targetMsg == 'export') {
          this.exportAjax()
        }
      },
      operationData(type) {
        let sAy = [], hdData;
        hdData = {};
        for (let obj of this.tableColumnData) {
          hdData[obj.en] = obj.zh;
        }
        sAy.push(hdData);
        for (let obj of this.headerButtonData.studentBasicMsg) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.roleInformation', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
        if (this.dataHeader.gradeIdValue) {
          this.sendLoadAjax();
        }
        else {
          this.vmMsgWarning('请选择年级!');
          return false;
        }
      },
      /*重置点击事件*/
      /*      resetClick(){
       this.$refs['studentMessge'].resetFields();
       this.headerButtonData.studentBasicMsg=[];
       },*/
      /*查询点击事件*/
      searchClick(){
        if (this.dataHeader.gradeIdValue) {
          this.sendLoadAjax();
        } else {
          this.vmMsgWarning('请选择用户类型！');
        }
      },
      /*send ajax------------*/
      /*得到年级*/
      getUserTypeAjax(){
        roleInformationUser().then((data) => {
          if (data.statu) {
            this.headerButtonData.gradeloadData = data.data;
          }
          else {
              this.vmMsgError('数据加载失败!');
          }
        });
      },
      sendLoadAjax(){
        this.loading=true;
        roleInformationLoad({
          nameId: this.dataHeader.gradeIdValue,
          pageSize: this.currentSize,
          value: this.fuzzyInput,
          sortType: this.orderBy,
          sort: this.sort,
          page: this.currentPage
        }).then((data) => {
          this.loading=false;
          this.currentPage = Number(data.page);
          this.pageAll = Number(data.maxpage);
          this.headerButtonData.studentBasicMsg = data.data;
          this.tableColumnData = data.tr;
        });
      },
      exportAjax(){
        if (this.dataHeader.gradeIdValue) {
          req.downloadFile('.g-container', '/school/user/userPassword?type=export&nameId=' + this.dataHeader.gradeIdValue, 'post');
        } else {
          this.vmMsgWarning('请选择用户类型！');
          return false;
        }
      },
      /*停用*/
      disabledPwdAjax(userId, msg){
        roleInformationDisabled({id: userId}).then(data => {
          if (data.statu) {
              this.vmMsgSuccess(msg + '成功！');
            this.sendLoadAjax();
          }
          else {
              this.vmMsgError(msg + '失败，请重试！');
          }
        });
      },
      /*重置密码*/
      updatePwdAjax(userId){
        roleInformationUpdataPwd({id: userId}).then(data => {
          if (data.statu) {
            this.vmMsgSuccess('重置密码成功！');
            this.sendLoadAjax();
          }
          else {
            this.vmMsgError('重置密码失败，请重试！');
          }
        });
      },
    },
    created(){
      this.getUserTypeAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/userManager/student/studentMessage.less';
  @import '../../../../style/userManager/student/studentManager.css';

  .g-buttonSearch {
    margin-left: 20/16rem;
  }
</style>


