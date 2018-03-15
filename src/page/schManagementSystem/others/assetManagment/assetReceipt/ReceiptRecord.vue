<template>
  <div class="g-container">
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
            <el-button class="filt buttonChild" @click="destoryAllClick" title="归还">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/ic_return_n.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/ic_return_highlight.png"/>
            </el-button>
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入需查询值"
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
        <el-table-column label="领用日期" min-width="150" prop="reciveTime" sortable="custom"></el-table-column>
        <el-table-column label="申请时间" min-width="150" prop="createTime" sortable="custom"></el-table-column>
        <el-table-column label="单价(元)" min-width="130" prop="onePrice" sortable="custom"></el-table-column>
        <el-table-column label="使用地址" min-width="150" prop="useAddress" sortable="custom"></el-table-column>
        <el-table-column label="领用人" min-width="120" prop="userName" sortable="custom"></el-table-column>
        <el-table-column label="创建人" min-width="120" prop="createUserId" sortable="custom"></el-table-column>
        <el-table-column label="说明" min-width="150" prop="explain" sortable="custom"></el-table-column>
        <el-table-column label="操作" min-width="150" fixed="right">
          <template slot-scope="props">
            <el-button type="text" v-if="props.row.reviceStatu==1" @click="revokeClick(props.row.reciveId)"
                       class="destoryColor">撤销
            </el-button>
            <el-button type="text" v-if="props.row.reviceStatu==3" disabled>已撤销</el-button>
            <el-button type="text" v-if="props.row.reviceStatu==6" disabled>未通过</el-button>
            <el-button type="text" v-if="props.row.reviceStatu==2&&props.row.approveStatu!=1&&props.row.backStatu!=2" @click="destoryClick(props.row.reciveId)"
                       class="greenButtonColor">归还
            </el-button>
            <el-button type="text" v-if="props.row.reviceStatu==5" disabled class="greenButtonColor">归还中</el-button>
            <el-button type="text" v-if="props.row.reviceStatu==4" disabled>已归还</el-button>
            <el-button type="text" v-if="props.row.reviceStatu!=5&&props.row.reviceStatu!=6&&props.row.approveStatu!=2&&props.row.approveStatu!=3&&props.row.backStatu!=2&&props.row.backStatu!=3" @click="handlerClick(props.$index)">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
    </section>
    <el-dialog class="headerNotBackground g-tree_content" title="信息编辑" :modal="false" :visible.sync="isDialog"
               :before-close="handlerClose">
      <el-form ref="gradeDialogForm" :model="gradeDialogForm" :rules="gradeDialogFormRules" label-width="100px"
               label-position="right">
        <el-form-item label="使用地址:" prop="useAddress">
          <el-input v-model="gradeDialogForm.useAddress" placeholder="请输入使用地址"></el-input>
        </el-form-item>
        <!-- <el-form-item label="领用时间:" prop="reciveTime">
          <el-date-picker :editable="false" type="datetime" placeholder="选择日期" v-model="gradeDialogForm.reciveTime"
                          style="width: 100%;"></el-date-picker>
        </el-form-item> -->
        <el-form-item label="说明:" prop="explain"> 
          <el-input v-model="gradeDialogForm.explain" placeholder="请输入使用说明"></el-input>
        </el-form-item>
      </el-form>
      <div class="g-button">
        <el-button type="primary" @click="confirmReceipt">确定</el-button>
        <el-button @click="cancelReceipt">取消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    ReceiptRecordGetLoad,//页面加载信息
    ReceiptRecordGetSort,//排序
    ReceiptRecordGetSearch,//模糊查询
    ReceiptRecordGetRevoke,//撤销
    ReceiptRecordGetReceipt,//归还
    ReceiptRecordHandler,//编辑
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
        multipleSelect: [],
        /*弹框*/
        isDialog: false,
        gradeDialogForm: {
          reciveId: '',
          useAddress: '',
          explain: '',
          reciveTime: ''
        },
        gradeDialogFormRules: {
        //   reciveTime: [
        //     {required: true, type: 'date', message: '请选择领用日期'}
        //   ],
          useAddress: [
            {min: 1, max: 30, message: '长度在 1 到 30 个字符', trigger: 'blur'}
          ],
          explain:[
            {min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur'}
          ]
        },
        createTime: '',
        /*tree*/
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'techerName',
        },
        /*ajax params*/
        revokeArr: [],
        destoryArr: [],
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
      confirmReceipt() {
        this.handlerAjax();
      },
      cancelReceipt() {
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
      /*tree点击事件*/
      handleNodeClick(data, node) {
      },
      /*table*/
      fuzzyTableClick() {
      },
      handleSelectionChange(val) {
        this.multipleSelect = val;
      },
      revokeClick(value) {
        this.$confirm("确定撤销该条数据吗？", "提示", {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then( () => {
          this.revokeHandler(value);
          this.revokeAjax();
        }).catch( () => {});
      },
      destoryClick(value) {
        this.$confirm("确定规范该资产吗？", "提示", {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then( () => {
          this.destoryHandler(value);
          this.destoryAjax();
        }).catch( () => {});
      },
      /*编辑*/
      handlerClick(index) {
        this.isDialog = true;
        this.createTime = this.classesTimeSetTable[index].createTime;
        this.gradeDialogForm.reciveId = this.classesTimeSetTable[index].reciveId;
        this.gradeDialogForm.reciveTime = this.classesTimeSetTable[index].reciveTime ? new Date(this.classesTimeSetTable[index].reciveTime) : '';
        this.gradeDialogForm.useAddress = this.classesTimeSetTable[index].useAddress;
        this.gradeDialogForm.explain = this.classesTimeSetTable[index].explain;
      },
      /*处理撤销和销毁的params*/
      revokeHandler(value) {
        this.revokeArr = [];
        if (value == 'all') {
          this.multipleSelect.forEach((value, index) => {
            this.revokeArr.push(value.reciveId);
          });
        } else {
          this.revokeArr.push(value);
        }
      },
      destoryHandler(value) {
        this.destoryArr = [];
        if (value == 'all') {
          this.multipleSelect.forEach((value, index) => {
            this.destoryArr.push(value.reciveId);
          });
        } else {
          this.destoryArr.push(value);
        }
      },
      /*批量撤销与归还*/
      revokeAllClick() {
        if (this.multipleSelect.length > 0) {
          this.$confirm("确定要撤销所选项吗？", "提示", {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then( () => {
            this.revokeHandler('all');
            this.revokeAjax();
          }).catch(() => {});
        } else {
          this.vmMsgWarning( '请选择需撤销信息！' );
        }
      },
      destoryAllClick() {
        if (this.multipleSelect.length > 0) {
           this.$confirm("确定要归还所选项吗？", "提示", {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then( () => {
            this.destoryHandler('all');
            this.destoryAjax();
          }).catch(() => {});
        } else {
          this.vmMsgWarning( '请选择归还信息！' );
        }
      },
      /*send ajax*/
      getLoadData() {
        this.loading = true;
        ReceiptRecordGetLoad().then(data => {
          this.loading = false;
          this.classesTimeSetTable = handlerAjaxData(data);
        });
      },
      exportClick() {
        req.downloadFile('.g-container', '/school/Assets/assetBrrowAndRetrun?type=export', 'post');
      },
      /*模糊查询*/
      fuzzyClick() {
        /*模糊查询执行回调*/
        this.loading = true;
        ReceiptRecordGetSearch({valueData: this.fuzzyInput}).then(data => {
          this.loading = false;
          this.classesTimeSetTable = handlerAjaxData(data);
        });
      },
      sortChange(column, prop, order) {
        this.loading = true;
        ReceiptRecordGetSort({sort: column.prop, sortData: column.order}).then(data => {
          this.loading = false;
          this.classesTimeSetTable = handlerAjaxData(data);
        });
      },
      /*撤销*/
      revokeAjax() {
        ReceiptRecordGetRevoke({reciveId: this.revokeArr}).then(data => {
          this.revokeArr = [];
          if (data.statu) {
            this.vmMsgSuccess( '撤销成功！' );
            this.getLoadData();
          } else {
            this.vmMsgError( data.message );
          }
        });
      },
      /*归还*/
      destoryAjax() {
        ReceiptRecordGetReceipt({reciveId: this.destoryArr}).then(data => {
          this.destoryArr = [];
          if (data.statu) {
            this.vmMsgSuccess( '归还成功！' );
            this.getLoadData();
          } else {
            this.vmMsgError( data.message );
          }
        });
      },
      /*编辑*/
      handlerAjax() {
        this.$refs['gradeDialogForm'].validate((valid) => {
          if (valid) {
            if (new Date(this.gradeDialogForm.reciveTime).getTime() < new Date(this.createTime).getTime()) {
              this.vmMsgWarning( '领用时间不能小于申请时间！' );
              return false;
            }
            var tData = {};
            for (let name in this.gradeDialogForm) {
              if (name == 'reciveTime') {
                tData[name] = moment(this.gradeDialogForm.reciveTime).format('YYYY-MM-DD HH:mm:ss');
              } else {
                tData[name] = this.gradeDialogForm[name];
              }
            }
            ReceiptRecordHandler(tData).then(data => {
              if (data.statu) {
                this.isDialog = false;
                this.vmMsgSuccess( '修改成功！' );
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


