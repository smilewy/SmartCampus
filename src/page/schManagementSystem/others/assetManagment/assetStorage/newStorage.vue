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
        <el-form :model="newStorageForm" :rules="newStorageFormRules" ref="newStorageForm" label-width="85px"
                 label-position="right">
          <el-form-item label="资产名称:" prop="assetsName">
            <el-input v-model="newStorageForm.assetsName" placeholder="请填写资产名称"></el-input>
          </el-form-item>
          <el-form-item label="资产分类:" prop="assetType">
            <el-input v-model="newStorageForm.assetType" disabled placeholder="请从左侧选择资产分类"></el-input>
          </el-form-item>
          <el-form-item label="供应商:" prop="supplier">
            <el-input v-model="newStorageForm.supplier" placeholder="请输入供应商"></el-input>
          </el-form-item>
          <el-form-item label="数量:" prop="num">
            <el-input v-model="newStorageForm.num" placeholder="请输入数量"></el-input>
          </el-form-item>
          <el-form-item label="单位:" prop="unit">
            <el-input v-model="newStorageForm.unit" placeholder="请输入单个资产的最小单位。如：一箱有10本书。此处应填写'本'。"></el-input>
          </el-form-item>
          <el-form-item label="品牌型号:" prop="brandModel">
            <el-input v-model="newStorageForm.brandModel" placeholder="请输入品牌和型号"></el-input>
          </el-form-item>
          <el-form-item label="规格:" prop="spec">
            <el-input v-model="newStorageForm.spec" placeholder="请输入单位资产规格。如：一箱有6台电脑，此处应填写一台电脑的规格"></el-input>
          </el-form-item>
          <el-form-item label="单价:" prop="onePrice">
            <el-input v-model="newStorageForm.onePrice" placeholder="请输入该单位资产的价格"></el-input>
          </el-form-item>
          <el-form-item label="入库时间:" prop="inStorageTime">
            <el-date-picker type="datetime" placeholder="选择日期" v-model="newStorageForm.inStorageTime"
                            style="width: 100%;" :editable="false"></el-date-picker>
          </el-form-item>
          <el-form-item label="存放位置:" prop="storageLocation">
            <el-input v-model="newStorageForm.storageLocation" placeholder="请输入存放位置"></el-input>
          </el-form-item>
          <el-form-item label="备注:" prop="Remarks">
            <el-input v-model="newStorageForm.Remarks" placeholder="请输入备注"></el-input>
          </el-form-item>
        </el-form>
        <el-button class="largeButton" @click="saveSetting" type="primary">保存</el-button>
      </div>
    </section>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {
    newStorageGetAsset,//选择资产分类
    newStorageSave,//
  } from '@/api/http'
  import {handlerAjaxData} from '@/assets/js/common'
  import {fuzzyQuery} from '@/assets/js/fuzzyQuery'
  import moment from 'moment'

  export default {
    data() {
      var reg = /^([+-]?)\d*\.?\d+$/;
      var validateNum = (rule, value, callback) => {
        if (!reg.test(value)) {
          return callback(new Error('请输入数量，且为数字！'));
        } else {
          callback();
        }
      };
      return {
        /*表格header文本*/
        headerText: '',
        /*教师模糊查询*/
        fuzzyInput: '',
        /*form表单*/
        newStorageForm: {
          assetsName: '',
          assetType: '',
          assetsTypeId: '',//资产分类id
          supplier: '',
          num: '',
          unit: '',
          brandModel: '',
          spec: '',
          onePrice: '',
          inStorageTime: new Date(),
          storageLocation: '',
          Remarks: ''
        },
        newStorageFormRules: {
          num: [
            {required: true, validator: validateNum, trigger: 'blur'},
             {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ],
          assetType: [
            {required: true, message: '请输入资产分类', trigger: 'blur'},
          ],
          assetsName: [
            {required: true, message: '请输入资产名称', trigger: 'blur'},
            {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ],
          supplier: [
            {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ],
          unit: [
            {min: 1, max: 5, message: '长度在 1 到 5 个字符', trigger: 'blur'}
          ],
          brandModel: [
            {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ],
          spec:[
            {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ],
          onePrice: [
            { required: true, validator: validateNum, trigger: 'blur'},
            {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ],
          inStorageTime: [
            {required: true, trigger: 'blur'},
          ],
          storageLocation: [
            {min: 1, max: 30, message: '长度在 1 到 30 个字符', trigger: 'blur'}
          ],
          remarks:[
            {min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur'}
          ]
        },
        /*tree*/
        treeData: [],
        defaultProps: {
          children: 'childs',
          label: 'assetsTypeName',
        },
        loading: false
      }
    },
    methods: {
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
        /*点击节点回调*/
        /*点击名字发送请求，返回数据绑定在右边部分*/
        /*给每一级赋值一个唯一的id即可辨认点击的是几级*/
        this.newStorageForm.assetType = data.assetsTypeName;
        this.newStorageForm.assetsTypeId = data.assetsTypeId;
      },
      /*send ajax*/
      getLoadData() {
        this.loading = true;
        newStorageGetAsset().then(data => {
          this.loading = false;
          /*得到资产分类*/
          this.treeData = handlerAjaxData(data);
        });
      },
      /*保存*/
      saveSetting() {
        if (this.newStorageForm.assetType && this.newStorageForm.assetsTypeId) {
          this.$refs['newStorageForm'].validate((valid) => {
            if (valid) {
              let tData = {};
              for (let name in this.newStorageForm) {
                if (name == 'inStorageTime') {
                  tData[name] = this.newStorageForm[name] ? moment(this.newStorageForm[name]).format('YYYY-MM-DD HH:mm:ss') : '';
                } else {
                  tData[name] = this.newStorageForm[name];
                }
              }
              newStorageSave(tData).then(data => {
                if (data.statu) {
                  this.vmMsgSuccess( '保存成功！' );
                } else {
                  this.vmMsgError( data.message );
                }
              });
            } else {
              this.vmMsgError( '请将数据填写正确！' );
            }
          });
        }
        else {
          this.vmMsgWarning( '请选择资产分类！' );
        }
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

  div.g-container {
    padding: 0;
    width: 100%;
  }
</style>




