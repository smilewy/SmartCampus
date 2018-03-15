<template>
  <div class="g-container outrecord">
    <header class="g-textHeader">
      <div class="g-liOneRow g-sa_header_search">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="exportClick" class="filt buttonChild" title="导出">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"/>
            </el-button>
            <el-button class="filt buttonChild" @click="revokeAllClick" title="撤销">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/ic_repeal_n.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/ic_repeal_hignlight.png"/>
            </el-button>
            <el-button class="filt buttonChild" @click="destoryAllClick" title="销毁">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/ic_destroy_n.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/ic_destroy_highlight.png"/>
            </el-button>
          </el-button-group>

          <el-button-group class="elGroupButton_two">
            <el-button data-msg="copy" class="filt buttonChild" title="复制" @click="operationData('copy')">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
            </el-button>
            <el-button  data-msg="print" class="filt buttonChild" title="打印预览" @click="operationData('print')">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入需要查询的值"
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
        <el-table-column label="资产编号" min-width="150" prop="assetsId" sortable="custom"></el-table-column>
        <el-table-column label="分类代码" min-width="150" prop="assetsTypeId" sortable="custom"></el-table-column>
        <el-table-column label="出库日期" min-width="150" prop="outTime" sortable="custom"></el-table-column>
        <el-table-column label="创建日期" min-width="150" prop="approveTime" sortable="custom"></el-table-column>
        <el-table-column label="单价(元)" min-width="150" prop="onePrice" sortable="custom"></el-table-column>
        <el-table-column label="使用地址" min-width="150" prop="useAddress" sortable="custom"></el-table-column>
        <el-table-column label="负责人" min-width="120" prop="approver" sortable="custom"></el-table-column>
        <el-table-column label="创建人" min-width="120" prop="createUserName" sortable="custom"></el-table-column>
        <el-table-column label="说明" min-width="150" prop="explain" sortable="custom"></el-table-column>
        <el-table-column label="操作" width="180" fixed="right">
          <template slot-scope="props">
            <el-button type="text" @click="revokeClick(props.row.assetOutId)" v-if="!Number(props.row.ifRevoKe)"
                       class="destoryColor">撤销
            </el-button>
            <el-button type="text" disabled v-else>已撤销</el-button>
            <el-button type="text" @click="destoryClick(props.row.assetOutId)" v-if="!Number(props.row.ifDestroy)"
                       class="deleteColor">销毁
            </el-button>
            <el-button type="text" disabled v-else>已销毁</el-button>
            <el-button type="text" @click="handlerClick(props.$index)">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
    </section>
    <el-dialog class="headerNotBackground g-tree_content" title="信息编辑" :modal="false" :visible.sync="isDialog"
               :before-close="handlerClose">
      <el-form ref="gradeDialogForm" :rules="gradeDialogFormRule" :model="gradeDialogForm" label-width="100px"
               label-position="right">
        <el-form-item label="使用地址:" prop="useAddress">
          <el-input v-model="gradeDialogForm.useAddress" placeholder="请输入使用地址"></el-input>
        </el-form-item>
        <el-form-item label="说明:" prop="explain">
          <el-input v-model="gradeDialogForm.explain" placeholder="请输入说明"></el-input>
        </el-form-item>
        <el-form-item label="出库时间:" prop="outTime">
          <el-date-picker type="datetime" :editable="false" :pickerOptions="pickerOptions" placeholder="选择日期"
                          v-model="gradeDialogForm.outTime" style="width: 100%;"></el-date-picker>
        </el-form-item>
      </el-form>
      <div class="g-button">
        <el-button type="primary" @click="confirmChange">确定</el-button>
        <el-button @click="cancelHandler">取消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    outRecordGetLoad,//页面加载数据
    outRecordGetSearch,//模糊查询
    outRecordGetSort,//排序
    outRecordGetRevoke,//撤销
    outRecordGetDestory,//撤毁
    outRecordGetChange,//编辑
  } from '@/api/http'
  import {handlerAjaxData, cancelConfirm} from '@/assets/js/common'
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
        multipleSelect: [],
        /*弹框*/
        createTime: '',
        isDialog: false,
        gradeDialogForm: {
          useAddress: '',
          explain: '',
          outTime: '',
          assetOutId: '',
        },
        gradeDialogFormRule: {
          outTime: [{required: true, message: '请选择出库时间'}],
          useAddress: [
            {min: 1, max: 30, message: '长度在 1 到 30 个字符', trigger: 'blur'}
          ],
          explain:[
            {min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur'}
          ]
        },
        /*tree*/
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'techerName',
        },
        /*ajax params*/
        revokeArr: [],
        destoryArr: [],
        pickerOptions: {
          disabledDate(time) {
            return new Date(moment(time).format('YYYY-MM-DD')).getTime() < new Date(moment(Date.now()).format('YYYY-MM-DD')).getTime();
          }
        },
        loading: false
      }
    },
    methods: {
      operationData(type){
        let sAy = [], hdData = {
          assetsName: '资产名称',
          assetsNumber: '资产编号',
          assetsTypeId: '分类代码',
          outTime: '出库日期',
          approveTime: '创建日期',
          onePrice: '单价',
          useAddress: '使用地址',
          approver: '负责人',
          createUserName: '创建人',
          explain: '说明'
        };
        sAy.push(hdData);
        for (let obj of this.classesTimeSetTable) {
          let d = {};
          for (let name in hdData) {
              d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.outrecord', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*点击返回流程图按钮*/
      goBackChart() {
        this.$router.push({name: 'evaluationManagement'});
      },
      revokeAllClick() {
        let isIncludeDestroy = this.multipleSelect.some( o => o.ifDestroy == 1 );
        if( isIncludeDestroy ){
          this.vmMsgWarning( '无法撤销，选中的资产中包含已经销毁的资产，已销毁的资产不能撤销！' ); return;
        }
        if (this.multipleSelect.length > 0) {
          this.$confirm('确定撤销？（撤销后资产将回到库中）', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            this.revokeHandler('all');
            this.revokeAjax();
          }).catch(() => {
          });
        }
        else {
          this.vmMsgWarning( '请选择需撤销信息！' );
        }
      },
      destoryAllClick() {
        let isIncludeDestroy = this.multipleSelect.some( o => o.ifRevoKe == 1 );
        if( isIncludeDestroy ){
          this.vmMsgWarning( '无法销毁，选中的资产中包含已经撤销的资产，已撤销的资产不能销毁！' ); return;
        }
        if (this.multipleSelect.length > 0) {
          this.$confirm('确定要将所选项进行销毁？', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
              this.destoryHandler('all');
              this.destoryAjax();
          }).catch((e) => {
          });
        } else {
          this.vmMsgWarning( '请选择需销毁信息！' );
        }
      },
      /*弹框*/
      handlerClose(done) {
        done();
      },
      cancelHandler() {
        this.isDialog = false;
      },
      fuzzyDialogClick() {
        /*input框输入值改变触发的函数*/
        this.$refs['allMsg'].filter(this.fuzzyInput);
      },
      filterNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.techerName.indexOf(value) !== -1;
      },
      /*table*/
      fuzzyTableClick() {
      },
      handleSelectionChange(val) {
        this.multipleSelect = val;
      },
      handlerClick(index) {
        this.isDialog = true;
        this.createTime = this.classesTimeSetTable[index].approveTime;
        Object.keys(this.gradeDialogForm).forEach((value) => {
          this.gradeDialogForm[value] = this.classesTimeSetTable[index][value];
        });
        this.gradeDialogForm.outTime = this.gradeDialogForm.outTime ? new Date(this.gradeDialogForm.outTime) : '';
      },
      revokeClick(value) {
          let temp = this.classesTimeSetTable.find( o => o.assetOutId == value );
          if( temp ){
              if(temp.ifDestroy == 1){
                this.vmMsgWarning( '已销毁的资产不能撤销！' ); return;
              }
          }
        this.$confirm('确定撤销？（撤销后资产将回到库中）', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.revokeHandler(value);
          this.revokeAjax();
        }).catch(() => {
        });
      },
      destoryClick(value) {
          let temp = this.classesTimeSetTable.find( o => o.assetOutId == value );
          if( temp ){
              if(temp.ifRevoKe == 1){
                this.vmMsgWarning( '已撤销的资产不能销毁！' ); return;
              }
          }
        this.$confirm('确定要将所选项进行销毁？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.destoryHandler(value);
          this.destoryAjax();
        }).catch((e) => {
      	});
      },
      /*处理撤销和销毁的params*/
      revokeHandler(value) {
        this.revokeArr = [];
        if (value == 'all') {
          this.multipleSelect.forEach((value, index) => {
            this.revokeArr.push(value.assetOutId);
          });
        } else {
          this.revokeArr.push(value);
        }
      },
      destoryHandler(value) {
        this.destoryArr = [];
        if (value == 'all') {
          let isIncludeDestroy = this.multipleSelect.every( o => o.ifDestroy != 1 );
          if( !isIncludeDestroy ) {
            this.vmMsgWarning( '无法删除, 已选择的数据中存在已经销毁的数据！' ); return;
          }
          this.destoryArr = this.multipleSelect.map( obj => obj.assetOutId );
        } else {
          this.destoryArr.push(value);
        }
      },
      /*send ajax*/
      exportClick() {
        req.downloadFile('.g-container', '/school/Assets/assetOut?type=export', 'post');
      },
      getLoadData() {
        this.loading = true;
        outRecordGetLoad().then(data => {
          this.loading = false;
          this.classesTimeSetTable = handlerAjaxData(data);
        })
      },
      /*模糊查询*/
      fuzzyClick() {
        /*模糊查询执行回调*/
        outRecordGetSearch({valueData: this.fuzzyInput}).then(data => {
          this.classesTimeSetTable = handlerAjaxData(data);
        });
      },
      /*排序*/
      sortChange(column, prop, order) {
        outRecordGetSort({sort: column.prop, sortData: column.order}).then(data => {
          this.classesTimeSetTable = handlerAjaxData(data);
        });
      },
      /*撤销*/
      revokeAjax() {
        outRecordGetRevoke({assetOutId: this.revokeArr}).then(data => {
          this.revokeArr = [];
          if (data.statu) {
            this.vmMsgSuccess( '撤销成功！' );
            this.getLoadData();
          } else {
            this.vmMsgError( data.message );
          }
        });
      },
      /*销毁*/
      destoryAjax() {
        if(this.destoryArr.length <= 0){ return; }
        outRecordGetDestory({assetOutId: this.destoryArr}).then(data => {
          this.destoryArr = [];
          if (data.statu) {
            this.vmMsgSuccess( '销毁成功！' );
            this.getLoadData();
          } else {
            this.vmMsgError( data.message );
          }
        });
      },
      confirmChange() {
        this.$refs['gradeDialogForm'].validate((vaild) => {
          if (vaild) {
            var dData = {};
            for (let name in this.gradeDialogForm) {
              if (name == 'outTime') {
                dData[name] = moment(this.gradeDialogForm.outTime).format('YYYY-MM-DD HH:mm:ss');
              } else {
                dData[name] = this.gradeDialogForm[name];
              }
            }
            if (new Date(dData.outTime).getTime() < new Date(this.createTime).getTime()) {
              this.vmMsgWarning( '出库日期不能小于创建日期！' );
              return false;
            }
            outRecordGetChange(dData).then(data => {
              if (data.statu) {
                this.vmMsgSuccess( '修改成功！' );
                this.isDialog = false;
                this.getLoadData();
              } else {
                this.vmMsgError( data.message );
              }
            })
          } else {
            this.vmMsgError( '请将数据填写正确！' );
          }
        })
      },
    },
    created() {
      this.getLoadData();
    },
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


