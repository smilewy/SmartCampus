<template>
  <div class="g-container storagerecord">
    <header class="g-textHeader">
      <div class="g-liOneRow g-sa_header_search">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button  @click="exportClick" class="filt buttonChild" title="导出">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
            </el-button>
            <el-button @click="deleteClick" class="filt buttonChild" title="删除">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete_highlight.png" />
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
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入搜索信息" @change="fuzzyClick"></el-input>
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
        <el-table-column label="序号" type="index" width="80"></el-table-column>
        <el-table-column label="资产名称" prop="assetsName" min-width="150" sortable="custom"></el-table-column>
        <el-table-column label="资产编号" prop="assetsNumber" min-width="150" sortable="custom"></el-table-column>
        <el-table-column label="资产分类" prop="assetsTypeName" min-width="150" sortable="custom"></el-table-column>
        <el-table-column label="分类代码" prop="assetsTypeId" min-width="120" sortable="custom"></el-table-column>
        <el-table-column label="入库日期" prop="inStorageTime" min-width="180" sortable="custom"></el-table-column>
        <el-table-column label="创建时间" prop="createTime" min-width="180" sortable="custom"></el-table-column>
        <el-table-column label="供应商" prop="supplier" min-width="200" sortable="custom"></el-table-column>
        <el-table-column label="规格" prop="spec" min-width="120" sortable="custom"></el-table-column>
        <el-table-column label="单位" prop="unit" min-width="100" sortable="custom"></el-table-column>
        <el-table-column label="品牌型号" prop="brandModel" min-width="120" sortable="custom"></el-table-column>
        <el-table-column label="单价" prop="onePrice" min-width="100" sortable="custom"></el-table-column>
        <el-table-column label="存放地址" prop="storageLocation" min-width="120" sortable="custom"></el-table-column>
        <el-table-column label="备注" prop="remarks" min-width="120" sortable="custom"></el-table-column>
        <el-table-column label="操作" fixed="right">
          <template slot-scope="props">
            <el-button type="text" :data-msg="props.row.assetsId" @click="handlerClick">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
    </section>
    <el-dialog class="headerNotBackground g-tree_content" title="入库信息编辑" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
      <div class="g-liOneRow">
        <div class="g-sectionR alertsList">
          <el-form :model="updateParams" :rules="newStorageFormRules" ref="storageForm" label-width="100px" label-position="right">
            <el-form-item label="资产名称:" prop="assetsName">
              <el-input v-model="updateParams.assetsName" placeholder="请填写资产名称"></el-input>
            </el-form-item>
            <el-form-item label="资产分类:" prop="assetsTypeName">
              <el-input v-model="updateParams.assetsTypeName" disabled placeholder="请从左侧选择资产分类"></el-input>
            </el-form-item>
            <el-form-item label="供应商:" prop="supplier">
              <el-input v-model="updateParams.supplier" placeholder="请输入供应商"></el-input>
            </el-form-item>
            <el-form-item label="单位:" prop="unit">
              <el-input v-model="updateParams.unit" placeholder="请输入单个资产的最小单位。如：一箱有10本书。此处应填写'本'。"></el-input>
            </el-form-item>
            <el-form-item label="品牌型号:" prop="brandModel">
              <el-input v-model="updateParams.brandModel" placeholder="请输入品牌和型号"></el-input>
            </el-form-item>
            <el-form-item label="规格:" prop="spec">
              <el-input v-model="updateParams.spec" placeholder="请输入单位资产规格。如：一箱有6台电脑，此处应填写一台电脑的规格"></el-input>
            </el-form-item>
            <el-form-item label="单价:" prop="onePrice">
              <el-input v-model="updateParams.onePrice" placeholder="请输入该单位资产的价格"></el-input>
            </el-form-item>
            <el-form-item label="入库时间:" prop="inStorageTime">
              <el-date-picker type="datetime" placeholder="选择日期" v-model="updateParams.inStorageTime" :editable="false" style="width: 100%;"></el-date-picker>
            </el-form-item>
            <el-form-item label="存放位置:" prop="storageLocation">
              <el-input v-model="updateParams.storageLocation" placeholder="请输入存放位置"></el-input>
            </el-form-item>
            <el-form-item label="备注:" prop="remarks">
              <el-input v-model="updateParams.remarks" placeholder="请输入备注"></el-input>
            </el-form-item>
          </el-form>
        </div>
        <div class="g-sectionL">
          <header class="gL-header">
            <h2>选择资产分类</h2>
            <el-input @input="fuzzyDialogClick" v-model="fuzzyDialogInput" class="fuzzyInput" placeholder="请输入查询类别" suffix-icon="el-icon-search"></el-input>
          </header>
          <section class="gL-section">
            <el-tree :highlight-current="true"  :expand-on-click-node="false" :data="treeData" :props="defaultProps" ref="allMsg" :filter-node-method="filterNode" @node-click="handleNodeClick"></el-tree>
          </section>
        </div>
      </div>
      <div class="g-button">
        <el-button type="primary" @click="saveUpdate">确定</el-button>
        <el-button @click="cancelSaveClick">取消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    newStorageGetAsset,//得到资产分类
    storageRecordLoad,//得到入库记录页面加载数据
    storageRecordDelete,//删除
    storageRecordUpdate,//修改
    storageRecordSearch,//模糊查询
    storageRecordSort,//排序
  } from '@/api/http'
  import req from '@/assets/js/common'
  import {handlerAjaxData} from '@/assets/js/common'
  export default{
    data(){
      var reg = /^([+-]?)\d*\.?\d+$/;
      var validateNum = (rule, value, callback) => {
        if (!reg.test(value)) {
          return callback(new Error('请输入数量，且为数字！'));
        } else {
          callback();
        }
      };
      return{
        /*模糊查询*/
        fuzzyInput:'',
        /*table*/
        classesTimeSetTable:[],
        multipleSelete:[],
        /*弹框*/
        isDialog:false,
        /*教师模糊查询*/
        fuzzyDialogInput:'',
        updateParams:{},//修改数据时的传参
        /*tree*/
        treeData:[],
        defaultProps: {
          children: 'childs',
          label:'assetsTypeName',
        },
        newStorageFormRules: {
          assetsName: [
            {required: true, message: '请输入资产名称', trigger: 'blur'},
            {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ],
          assetsTypeName: [
            {required: true, message: "请选择资产分类", trigger: 'blur'},
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
        loading: false
      }
    },
    methods:{
      operationData(type){
        let sAy = [], hdData = {
          assetsName: '资产名称',
          assetsNumber: '资产编号',
          assetsTypeName: '资产分类',
          assetsTypeId: '分类代码',
          inStorageTime: '入库日期',
          createTime: '创建时间',
          supplier: '供应商',
          spec: '规格',
          unit: '单位',
          brandModel: '品牌型号',
          onePrice: '单价',
          num: '数量',
          storageLocation: '存放地址',
          remarks: '备注'
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
          req.copyTableData('.storagerecord', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
        storageRecordSearch({valueData:this.fuzzyInput}).then(data=>{
          this.classesTimeSetTable=handlerAjaxData(data);
        });
      },
      /*弹框*/
      handlerClose(done){
        done();
      },
      fuzzyDialogClick(){
        /*input框输入值改变触发的函数*/
        this.$refs['allMsg'].filter(this.fuzzyDialogInput);
      },
      filterNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.assetsTypeName.indexOf(value) !== -1;
      },
      cancelSaveClick(){
        this.isDialog=false;
      },
      /*tree点击事件*/
      handleNodeClick(data,node){
        /*点击节点回调*/
        /*点击名字发送请求，返回数据绑定在右边部分*/
        /*给每一级赋值一个唯一的id即可辨认点击的是几级*/
        this.updateParams.assetsTypeId=data.assetsTypeId;
        this.updateParams.assetsTypeName=data.assetsTypeName;
      },
      /*table*/
      handleSelectionChange(val){
        this.multipleSelete=val;
      },
      sortChange(column,prop,order){
        storageRecordSort({sort:column.prop,sortData:column.order}).then(data=>{
          this.classesTimeSetTable=handlerAjaxData(data);
        });
      },
      handlerClick(event){
        let e=$(event.currentTarget);
        this.isDialog=true;
        /*得到资产分类*/
        this.getAssetsData();
        /*得到修改默认数据*/
        this.classesTimeSetTable.forEach((value,index)=>{
          if(value.assetsId==Number(e.data('msg'))){
            this.updateParams=JSON.parse(JSON.stringify(value));
          }
        });
      },
      /*send ajax-------*/
      exportClick(){
        req.downloadFile('.g-container','/school/Assets/assetStoreroom?type=export','post');
      },
      sendLoadAjax(){
        this.loading = true;
        storageRecordLoad().then(data=>{
          this.loading = false;
          this.classesTimeSetTable=handlerAjaxData(data);
        })
      },
      deleteClick(){
        if(this.multipleSelete.length>0){
          let deleteArr=[];
          this.$confirm('确定删除？此操作无法撤回！','提示',{
            confirmButtonText:'确定',
            cancelButtonText: '取消',
            type:'warning',
          }).then(()=>{
            /*确定删除的操作*/
            /*处理参数数据*/
            this.multipleSelete.forEach((value,index)=>{
              if(Object.keys(value).includes('assetsId')){
                deleteArr.push(value.assetsId);
              }
            });
            /*发送请求*/
            storageRecordDelete({assetsId:deleteArr}).then(data=>{
              if(data.statu){
                this.vmMsgSuccess( '删除成功！' );
                /*刷新页面*/
                this.sendLoadAjax();
              }else{
                this.vmMsgError( '删除失败！' );
              }
            });
          }).catch(()=>{});
        }else{
          this.vmMsgWarning( '请选择需要删除的项！' );
        }
      },
      /*得到资产分类*/
      getAssetsData(){
        newStorageGetAsset().then(data=>{
          /*得到资产分类*/
          this.treeData=handlerAjaxData(data);
        });
      },
      saveUpdate(){
        this.$refs.storageForm.validate((valid) => {
          if (valid) {
            this.isDialog=false;
            storageRecordUpdate({data:this.updateParams}).then(data=>{
              if(data.statu){
                this.vmMsgSuccess( '修改成功！' );
                /*刷新页面*/
                this.sendLoadAjax();
              }else{
                this.vmMsgError( '修改失败！' );
              }
            });
          } else {
            this.vmMsgError( '请将数据填写正确！' );
          }
        });
        
      },
    },
    created(){
      this.sendLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/test';
  @import '../../../../../style/style';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  div.g-container{padding:0;width:100%;}
  .headerNotBackground.g-tree_content{
    .g-sectionR{.width(585,940);margin:0;}
    .g-sectionL{.width(330,940);.NotLineheight(541);}
  }
</style>


