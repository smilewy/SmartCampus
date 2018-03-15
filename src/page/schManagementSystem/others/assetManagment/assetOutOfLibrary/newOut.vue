<template>
  <div class="g-container">
    <section class="g-tree_content">
      <div class="g-sectionL">
        <header class="gL-header">
          <h2>选择资产分类</h2>
          <el-input @input="fuzzyClick" v-model="fuzzyInput" class="fuzzyInput" placeholder="请输入查询类别"
                    suffix-icon="el-icon-search"></el-input>
        </header>
        <section class="gL-section">
          <el-tree 
              v-loading="loading" 
              element-loading-text="拼命加载中" 
              element-loading-spinner="el-icon-loading" 
              :highlight-current="true" 
              :expand-on-click-node="false" 
              :data="treeData" 
              :props="defaultProps"
              ref="allMsg" 
              :filter-node-method="filterNode" 
              @node-click="handleNodeClick">
          </el-tree>
        </section>
      </div>
      <div class="g-sectionR alertsList">
        <header class="g-liOneRow">
          <el-button type="primary" :disabled="isAllStorage" @click="outStorageAllClick">批量出库</el-button>
          <div class="gs-refresh g-fuzzyInput">
            <el-input type="text" v-model="fuzzyTableInput" suffix-icon="el-icon-search" placeholder="请选择搜索值"
                      @change="fuzzyTableClick"></el-input>
          </div>
        </header>
        <el-table class="g-NotHover" 
                  v-loading="loading1" 
                  element-loading-text="拼命加载中" 
                  element-loading-spinner="el-icon-loading" 
                  max-height="550" 
                  :data="newStoragetable" 
                  @sort-change="sortChange">
          <el-table-column>
            <template slot-scope="scope">
              <el-checkbox :disabled="scope.row.ifRecive=='0'" v-model="scope.row.checked"></el-checkbox>
            </template>
          </el-table-column>
          <el-table-column label="序号" type="index" width="100px"></el-table-column>
          <el-table-column label="资产名称" min-width="150" prop="assetsName" sortable="custom"></el-table-column>
          <el-table-column label="资产编号" min-width="150" prop="assetsNumber" sortable="custom"></el-table-column>
          <el-table-column label="分类代码" min-width="150" prop="assetsTypeId" sortable="custom"></el-table-column>
          <el-table-column label="单价（元）" min-width="150" prop="onePrice" sortable="custom"></el-table-column>
          <el-table-column label="存放位置" min-width="150" prop="storageLocation" sortable="custom"></el-table-column>
          <el-table-column label="品牌型号" min-width="150" prop="brandModel" sortable="custom"></el-table-column>
          <el-table-column label="规格" min-width="120" prop="spec" sortable="custom"></el-table-column>
          <el-table-column label="供应商" min-width="120" prop="supplier" sortable="custom"></el-table-column>
          <el-table-column label="领用状态" min-width="120">
            <template slot-scope="prop">
              <span v-if="Number(prop.row.ifRecive)">已领用</span>
              <span v-else>未领用</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" fixed="right">
            <template slot-scope="prop">
              <el-button :disabled="!Number(prop.row.ifRecive)" type="text" @click="outStorageClick(prop.$index)">出库
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <el-dialog class="headerNotBackground" :title="dialogTitle" :modal="false" :visible.sync="isDialog"
                 :before-close="handlerClose">
        <el-form :model="gradeDialogForm" :rules="rules" ref="gradeDialogForm" label-width="100px"
                 label-position="right">
          <el-form-item label="负责人:" prop="approverId" required>
            <el-select v-model="gradeDialogForm.approverId" style="width:100%;">
              <el-option v-for="(content,index) in approvelData" :key="index" :label="content.name"
                         :value="content.id"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="使用地址:" prop="useAddress">
            <el-input v-model="gradeDialogForm.useAddress" placeholder="请输入使用地址"></el-input>
          </el-form-item>
          <el-form-item label="说明:" prop="explain">
            <el-input v-model="gradeDialogForm.explain" placeholder="请输入使用地址"></el-input>
          </el-form-item>
          <el-form-item label="出库日期:" prop="outTime" required>
            <el-date-picker type="datetime" :editable="false" placeholder="选择日期" :picker-options="pickerOptions"
                            v-model="gradeDialogForm.outTime" style="width: 100%;"></el-date-picker>
          </el-form-item>
        </el-form>
        <div class="g-button">
          <el-button type="primary" @click="confirmOutStorage">确定出库</el-button>
          <el-button @click="cancelOutStorage">取消</el-button>
        </div>
      </el-dialog>
    </section>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {
    newOutGetAsset,//得到资产分类
    newOutGetLoad,//页面加载信息table
    newOutGetOutStorage,//出库
    newOutGetOutApprovel,//得到负责人
    newOutGetSearch,//模糊查询
    newOutGetSort,//排序
  } from '@/api/http'
  import {fuzzyQuery} from '@/assets/js/fuzzyQuery'
  import {handlerAjaxData} from '@/assets/js/common'
  import req from '@/assets/js/common'
  import moment from 'moment'

  export default {
    data() {
      return {
        /*教师模糊查询*/
        fuzzyInput: '',
        /*table表单*/
        newStoragetable: [],
        /*tree*/
        treeData: [],
        defaultProps: {
          children: 'childs',
          label: 'assetsTypeName',
        },
        /*批量出库*/
        isAllStorage: true,
        /*右边*/
        /*模糊查询*/
        fuzzyTableInput: '',
        /*弹框*/
        isDialog: false,
        dialogTitle: '出库',
        gradeDialogForm: {
          useAddress: '',
          explain: '',
          outTime: '',
          assetsId: [],//资产分类id
          approverId: ''
        },
        approvelData: [],
        rules: {
          approverId: [{required: true, message: '请选择负责人'}],
          outTime: [{required: true, message: '请选择出库时间'}],
          useAddress: [
            {min: 1, max: 30, message: '长度在 1 到 30 个字符', trigger: 'blur'}
          ],
          explain:[
            {min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur'}
          ]
        },
        pickerOptions: {
          disabledDate(time) {
            return new Date(moment(time).format('YYYY-MM-DD')).getTime() < new Date(moment(Date.now()).format('YYYY-MM-DD')).getTime();
          }
        },
        loading: false,
        loading1: false
      }
    },
    methods: {
      /*点击返回流程图按钮*/
      goBackChart() {
        this.$router.push({name: 'examinationChart'});
      },
      /*教师信息模糊查询*/
      fuzzyClick() {
        /*input框输入值改变触发的函数*/
        this.$refs['allMsg'].filter(this.fuzzyInput);
      },
      filterNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.assetsTypeName.indexOf(value) !== -1;
      },
      /*tree点击事件*/
      handleNodeClick(data, node) {
        this.loading1 = true;
        this.assetsTypeId = data.assetsTypeId;
        this.getTableLoadData();
      },
      /*批量出库*/
      outStorageAllClick() {
        let sAry = [];
        for (let obj of this.newStoragetable) {
          if (obj.checked) {
            sAry.push(obj.assetsId);
          }
        }
        if (sAry.length <= 0) {
          this.vmMsgWarning( '请选择需要批量出库的数据！' );
          return false;
        }
        this.isDialog = true;
        this.dialogTitle = '批量出库';
        this.gradeDialogForm.assetsId = sAry;
      },
      /*table*/
      outStorageClick(index) {
        this.isDialog = true;
        this.dialogTitle = '出库';
        this.gradeDialogForm.assetsId = [];
        this.gradeDialogForm.assetsId.push(this.newStoragetable[index].assetsId);
      },
      /*弹框*/
      handlerClose(done) {
        this.$refs['gradeDialogForm'].resetFields();
        done();
      },
      cancelOutStorage() {
        this.isDialog = false;
        this.$refs['gradeDialogForm'].resetFields();
      },
      /*检测批量出库是否可用*/
      checkAllStorage() {
        this.isAllStorage = true;//禁用
        for (let obj of this.newStoragetable) {
          if (obj.ifRecive == '1') {
            this.isAllStorage = false;//不禁用
            return false;
          }
        }
      },
      /*send ajax----------------*/
      getLoadAjax() {
        this.loading = true;
        newOutGetAsset().then(data => {
          this.loading = false;
          /*得到资产分类*/
          this.treeData = handlerAjaxData(data) || [];
        });
      },
      sortChange(column, prop, order) {
        this.loading1 = true;
        newOutGetSort({assetsTypeId: this.assetsTypeId, sort: column.prop, sortData: column.order}).then(data => {
          this.loading1 = false;
          let dData = handlerAjaxData(data);
          for (let obj of dData) {
            obj.checked = false;
          }
          this.newStoragetable = dData;
        });
      },
      getTableLoadData() {
        this.loading1 = true;
        newOutGetLoad({assetsTypeId: this.assetsTypeId}).then(data => {
          this.loading1 = false;
          let dData = handlerAjaxData(data);
          for (let obj of dData) {
            obj.checked = false;
          }
          this.newStoragetable = dData;
          this.checkAllStorage();
        });
      },
      /*模糊查询*/
      fuzzyTableClick() {
        this.loading1 = true;
        newOutGetSearch({assetsTypeId: this.assetsTypeId, valueData: this.fuzzyTableInput}).then(data => {
          this.loading1 = false;
          let dData = handlerAjaxData(data);
          for (let obj of dData) {
            obj.checked = false;
          }
          this.newStoragetable = dData;
        });
      },
      /*得到负责人*/
      getApprovelData() {
        newOutGetOutApprovel().then(data => {
          this.approvelData = handlerAjaxData(data);
        });
      },
      confirmOutStorage() {
        this.$refs['gradeDialogForm'].validate((vaild) => {
          if (vaild) {
            let dData = {};
            for (let name in this.gradeDialogForm) {
              if (name == 'outTime') {
                dData[name] = moment(this.gradeDialogForm[name]).format('YYYY-MM-DD HH:mm:ss');
              } else {
                dData[name] = this.gradeDialogForm[name];
              }
            }
            newOutGetOutStorage(dData).then(data => {
              if (data.statu) {
                this.vmMsgSuccess( '出库操作成功！' );
                this.$refs['gradeDialogForm'].resetFields();
                this.isDialog = false;
                this.getTableLoadData();
              } else {
                this.vmMsgError( data.message );
              }
            });
            this.$refs['gradeDialogForm'].resetFields();
          } else {
            this.vmMsgError( '请将数据填写正确！');
          }
        });
      },
    },
    created() {
      this.getLoadAjax();
      this.getApprovelData();//得到负责人
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/test';
  @import '../../../../../style/style';

  div.g-container {
    padding: 0;
    width: 100%;
  }

  .g-tree_content .g-sectionR > header.g-liOneRow {
    border: none;
  }
</style>




