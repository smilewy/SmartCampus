<template>
  <div class="g-container">
    <header class="g-textHeader">
      <div class="g-liOneRow g-sa_header_search">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button class="filt buttonChild" @click="exportClick" title="导出">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"/>
            </el-button>
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入需搜索值"
                    @change="fuzzyClick"></el-input>
        </div>
      </div>
    </header>
    <section class="centerTable alertsList">
      <el-table class="g-NotHover" 
                v-loading="loading" 
                element-loading-text="拼命加载中" 
                element-loading-spinner="el-icon-loading" 
                :data="classesTimeSetTable" 
                :max-height="550"
                @selection-change="handleSelectionChange"
                @sort-change="sortChange">
        <el-table-column type="selection"></el-table-column>
        <el-table-column label="序号" type="index" width="100px"></el-table-column>
        <el-table-column label="资产名称" min-width="150" prop="assetsName" sortable="custom"></el-table-column>
        <el-table-column label="资产编号" min-width="150" prop="assetsNumber" sortable="custom"></el-table-column>
        <el-table-column label="分类代码" min-width="150" prop="assetsTypeId" sortable="custom"></el-table-column>
        <el-table-column label="归还日期" min-width="150" prop="backTime" sortable="custom"></el-table-column>
        <el-table-column label="创建日期" min-width="150" prop="createTime" sortable="custom"></el-table-column>
        <el-table-column label="单价(元)" min-width="130" prop="onePrice" sortable="custom"></el-table-column>
        <el-table-column label="创建人" min-width="150" prop="createUserName" sortable="custom"></el-table-column>
        <el-table-column label="归还人" min-width="150" prop="backUserName" sortable="custom"></el-table-column>
        <el-table-column label="说明" min-width="150" prop="backExplain" sortable="custom"></el-table-column>
        <el-table-column label="操作" min-width="120" fixed="right">
          <template slot-scope="props">
            <el-button type="text" @click="handlerClick(props.$index)">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
    </section>
    <el-dialog class="headerNotBackground g-tree_content" title="信息编辑" :modal="false" :visible.sync="isDialog"
              :before-close="handlerClose">
      <el-form ref="gradeDialogForm" :model="gradeDialogForm" :rules="gradeDialogFormRule" label-width="100px"
               label-position="right">
        <!-- <el-form-item label="归还时间:" prop="backTime">
          <el-date-picker type="datetime" :editable="false" placeholder="选择日期" v-model="gradeDialogForm.backTime"
                          style="width: 100%;"></el-date-picker>
        </el-form-item> -->
        <el-form-item label="说明:" prop="backExplain">
          <el-input v-model="gradeDialogForm.backExplain" placeholder="请输入归还说明"></el-input>
        </el-form-item>
      </el-form>
      <div class="g-button">
        <el-button type="primary" @click="handlerAjax()">确定</el-button>
        <el-button @click="cancelHandler">取消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    returnRecordGetLoad,//页面加载信息
    returnRecordGetSort,//排序
    returnRecordGetSearch,//模糊查询
    returnRecordHanlder,//编辑
  } from '@/api/http'
  import {handlerAjaxData} from '@/assets/js/common'
  import req from '@/assets/js/common'
  import moment from 'moment'

  export default {
    data() {
      return {
        /*模糊查询*/
        fuzzyInput: '',
        evaluationName: '',
        /*table*/
        classesTimeSetTable: [],
        /*弹框*/
        isDialog: false,
        createTime: '',
        gradeDialogForm: {
          reciveId: '',
          backTime: '',
          backExplain: ''
        },
        gradeDialogFormRule: {
        //   backTime: [
        //     {required: true, type: 'date', message: '请选择领用日期'}
        //   ],
          backExplain: [
            {min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur'}
          ]
        },
        loading: false
      }
    },
    methods: {
      /*点击返回流程图按钮*/
      goBackChart() {
        this.$router.push({name: 'evaluationManagement'});
      },
      /*弹框*/
      handlerClose(done) {
        done();
      },
      fuzzyDialogClick() {
        /*input框输入值改变触发的函数*/
        this.$refs['allMsg'].filter(this.fuzzyInput);
      },
      cancelHandler() {
        this.isDialog = false;
      },
      /*table*/
      fuzzyTableClick() {
      },
      handleSelectionChange(val) {
      },
      handlerClick(index) {
        this.isDialog = true;
        this.createTime = this.classesTimeSetTable[index].createTime;
        Object.keys(this.gradeDialogForm).forEach((value) => {
          this.gradeDialogForm[value] = this.classesTimeSetTable[index][value];
        });
        this.gradeDialogForm.backTime = this.classesTimeSetTable[index].backTime ? new Date(this.classesTimeSetTable[index].backTime) : ''
      },
      /*send ajax*/
      exportClick() {
        req.downloadFile('.g-container', '/school/Assets/assetBrrowAndRetrun?type=exportReurnCecord', 'post');
      },
      getLoadData() {
        this.loading = true;
        returnRecordGetLoad().then(data => {
          this.loading = false;
          this.classesTimeSetTable = handlerAjaxData(data);
        });
      },
      /*模糊查询*/
      fuzzyClick() {
        /*模糊查询执行回调*/
        this.loading = true;
        returnRecordGetSearch({valueData: this.fuzzyInput}).then(data => {
          this.loading = false;
          this.classesTimeSetTable = handlerAjaxData(data);
        });
      },
      sortChange(column, prop, order) {
        this.loading = true;
        returnRecordGetSort({sort: column.prop, sortData: column.order}).then(data => {
          this.loading = false;
          this.classesTimeSetTable = handlerAjaxData(data);
        });
      },
      /*编辑*/
      handlerAjax() {
        this.$refs['gradeDialogForm'].validate((valid) => {
          if (valid) {
            if (new Date(this.gradeDialogForm.backTime).getTime() < new Date(this.createTime).getTime()) {
              this.vmMsgWarning( '归还时间不能小于创建时间！' );
              return false;
            }
            var tData = {};
            for (let name in this.gradeDialogForm) {
              if (name == 'backTime') {
                tData[name] = moment(this.gradeDialogForm.backTime).format('YYYY-MM-DD HH:mm:ss');
              } else {
                tData[name] = this.gradeDialogForm[name];
              }
            }
            returnRecordHanlder(tData).then(data => {
              if (data.statu) {
                this.vmMsgSuccess( '修改成功！' );
                this.isDialog = false;
                this.getLoadData();
              }
              else {
                this.vmMsgError( data.message );
              }
            });
          } else {
            this.vmMsgError( '请将数据填写正确！' );
          }
        });
      },
    },
    created() {
      this.getLoadData();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/test';
  @import '../../../../../style/style';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';

  .g-sa_header_search {
    .marginTop(32);
    .marginBottom(20);
  }

  div.g-container {
    padding: 0;
    width: 100%;
  }

  .headerNotBackground.g-tree_content {
    .g-sectionR {
      .width(585, 940);
      margin: 0;
    }
    .g-sectionL {
      .width(330, 940);
      .NotLineheight(541);
    }
  }
</style>


