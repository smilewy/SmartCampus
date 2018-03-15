<template>
  <div class="g-container">
    <section class="g-tree_content">
      <div class="g-sectionL">
        <header class="gL-header">
          <h2>选择资产分类</h2>
          <el-input @input="fuzzyClick" v-model="fuzzyInput" class="fuzzyInput" placeholder="请输入查询类别" suffix-icon="el-icon-search"></el-input>
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
          <el-button @click="receiptAllDialog" type="primary">批量领用</el-button>
          <div class="gs-refresh g-fuzzyInput">
            <el-input type="text" v-model="fuzzyTableInput" suffix-icon="el-icon-search" placeholder="请输入需要查询值" @change="fuzzyTableClick"></el-input>
          </div>
        </header>
        <el-table class="g-NotHover" 
                  v-loading="loading1" 
                  element-loading-text="拼命加载中" 
                  element-loading-spinner="el-icon-loading" 
                  :data="newStoragetable" 
                  :max-height="550"
                  @selection-change="handleSelectionChange" 
                  @sort-change="sortChange">
          <el-table-column type="selection"></el-table-column>
          <el-table-column label="序号" type="index" width="100px"></el-table-column>
          <el-table-column label="资产名称" min-width="150" prop="assetsName" sortable="custom"></el-table-column>
          <el-table-column label="资产编号" min-width="150" prop="assetsNumber" sortable="custom"></el-table-column>
          <el-table-column label="国际代码" min-width="150" prop="gbCode" sortable="custom"></el-table-column>
          <el-table-column label="单价（元）" min-width="130" prop="onePrice" sortable="custom"></el-table-column>
          <el-table-column label="存放位置" min-width="150" prop="storageLocation" sortable="custom"></el-table-column>
          <el-table-column label="品牌型号" min-width="150" prop="brandModel" sortable="custom"></el-table-column>
          <el-table-column label="规格" min-width="120" prop="spec" sortable="custom"></el-table-column>
          <el-table-column label="供应商" min-width="150" prop="supplier" sortable="custom"></el-table-column>
          <el-table-column label="单位" min-width="120" prop="unit" sortable="custom"></el-table-column>
          <el-table-column label="备注" min-width="120" prop="remarks" sortable="custom"></el-table-column>
          <el-table-column label="操作" min-width="120" fixed="right">
            <template slot-scope="prop">
              <el-button type="text" v-if="Number(prop.row.ifRecive)==0" @click="receiptDialog(prop.row.assetsId)">领用</el-button>
              <span v-if="Number(prop.row.ifRecive)==1">已领用</span>
              <span v-if="Number(prop.row.ifRecive)==2">申请中</span>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <el-dialog class="headerNotBackground" :title="receiptTitle" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
        <el-form :model="gradeDialogForm" :rules="rules" ref="gradeDialogForm" label-width="90px" label-position="right">
          <el-form-item label="领用人:" prop="userId" required>
            <el-select v-model="gradeDialogForm.userId" style="width:100%;">
              <el-option v-for="(content,index) in recieptArr" :key="index" :label="content.name" :value="content.id"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="使用地址:" prop="useAddress">
            <el-input v-model="gradeDialogForm.useAddress" placeholder="请输入使用地址"></el-input>
          </el-form-item>
          <el-form-item label="说明:" prop="explain">
            <el-input v-model="gradeDialogForm.explain" placeholder="请输入使用地址"></el-input>
          </el-form-item>
          <el-form-item label="领用日期:" prop="reciveTime" required>
            <el-date-picker type="datetime" :editable="false" :picker-options="pickerOptions" placeholder="选择日期" v-model="gradeDialogForm.reciveTime" style="width: 100%;"></el-date-picker>
          </el-form-item>
        </el-form>
        <div class="g-button">
          <el-button type="primary" @click="confirmReceipt">确定领用</el-button>
          <el-button @click="cancelReceipt">取消</el-button>
        </div>
      </el-dialog>
    </section>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {
    newReceiptGetAsset,//资产分类
    newReceiptGetLoad,//页面加载信息
    newReceiptGetSort,//排序
    newReceiptGetSearch,//模糊查询
    newReceiptGetReceipt,//新增领用
    newReceiptReceiptPerson,//得到领用人
  } from '@/api/http'
  import {handlerAjaxData} from '@/assets/js/common'
  import moment from 'moment'
  export default{
    data(){
      return{
        /*教师模糊查询*/
        fuzzyInput:'',
        /*table表单*/
        newStoragetable:[],
        /*tree*/
        treeData:[],
        defaultProps: {
          children: 'childs',
          label:'assetsTypeName',
        },
        /*右边*/
        /*模糊查询*/
        fuzzyTableInput:'',
        /*弹框*/
        isDialog:false,
        receiptTitle:'领用',
        gradeDialogForm:{
          reciveTime:'',
          useAddress:'',
          explain:'',
          userId:'',
          assetsId:[]
        },
        recieptArr:[],
        rules:{
          userId:[{required:true,message:'请选择领用人'}],
          reciveTime:[{required:true,type:'date',message:'请选择领用日期'}],
          useAddress: [
            {min: 1, max: 30, message: '长度在 1 到 30 个字符', trigger: 'blur'}
          ],
          explain:[
            {min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur'}
          ]
        },
        pickerOptions:{
          disabledDate(time) {
            return new Date(moment(time).format('YYYY-MM-DD')).getTime() < new Date(moment(Date.now()).format('YYYY-MM-DD')).getTime();
          }
        },
        /*send ajax params*/
        assetsTypeId:'',
        multipleData:[],
        loading: false,
        loading1: false
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'examinationChart'});
      },
      /*教师信息模糊查询*/
      fuzzyClick(){
        /*input框输入值改变触发的函数*/
        this.$refs['allMsg'].filter(this.fuzzyInput);
      },
      filterNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.assetsTypeName.indexOf(value) !== -1;
      },
      /*tree点击事件*/
      handleNodeClick(data,node){
        this.assetsTypeId=data.assetsTypeId;
        this.getTableData();
      },
      /*右边*/
      handleSelectionChange(val){
        this.multipleData=val;
      },
      /*弹框*/
      handlerClose(done){
        this.$refs['gradeDialogForm'].resetFields();
        done();
      },
      /*领用*/
      receiptDialog(assetsId){
        this.isDialog=true;
        this.receiptTitle='领用';
        this.gradeDialogForm.assetsId=[];
        this.gradeDialogForm.assetsId.push(assetsId);
        this.getReciptPerson();
      },
      /*批量领用*/
      receiptAllDialog(){
        if(this.multipleData.length>0){
          this.isDialog=true;
          this.receiptTitle='批量领用';
          this.gradeDialogForm.assetsId=[];
          this.multipleData.forEach((row,rowI)=>{
            this.gradeDialogForm.assetsId.push(row.assetsId);
          });
          this.getReciptPerson();
        }
        else{
          this.vmMsgWarning( '请选择批量领用项！' );
        }
      },
      confirmReceipt(){
        this.$refs['gradeDialogForm'].validate((valid)=>{
          if(valid){
            this.isDialog=false;
            this.receiptAllAjax();
            this.$refs['gradeDialogForm'].resetFields();
          }else{
            this.vmMsgError( '请将数据填写正确！' );
          }
        });
      },
      cancelReceipt(){
        this.isDialog=false;
        this.$refs['gradeDialogForm'].resetFields();
      },
      /*send ajax*/
      getLoadData(){
        this.loading = true;
        newReceiptGetAsset().then(data=>{
          this.loading = false;
          /*得到资产分类*/
          this.treeData=handlerAjaxData(data) || [];
        });
      },
      getTableData(){
        this.loading1 = true;
        newReceiptGetLoad({assetsTypeId:this.assetsTypeId}).then(data=>{
          this.loading1 = false;
          this.newStoragetable=handlerAjaxData(data);
        });
      },
      /*模糊查询*/
      fuzzyTableClick(){
        this.loading1 = true;
        newReceiptGetSearch({assetsTypeId:this.assetsTypeId,valueData:this.fuzzyTableInput}).then(data=>{
          this.loading1 = false;
          this.newStoragetable=handlerAjaxData(data);
        });
      },
      /*排序*/
      sortChange(column,prop,order){
        this.loading1 = true;
        newReceiptGetSort({assetsTypeId:this.assetsTypeId,sort:column.prop,sortData:column.order}).then(data=>{
          this.loading1 = false;
          this.newStoragetable=handlerAjaxData(data);
        });
      },
      /*得到领用人*/
      getReciptPerson(){
        newReceiptReceiptPerson().then(data=>{
          if(data.statu){
            this.recieptArr=data.data;
          }
          else{
            this.vmMsgError( '领用人数据加载失败！' );
          }
        });
      },
      /*领用与批量领用*/
      receiptAllAjax(){
        this.gradeDialogForm.reciveTime=moment(this.gradeDialogForm.reciveTime).format('YYYY-MM-DD HH:mm:ss')
        newReceiptGetReceipt(this.gradeDialogForm).then(data=>{
          if(data.statu){
            this.vmMsgSuccess( '领用成功！' );
            this.alertPrompt('领用成功！','success');
            this.getTableData();
          }
          else{
            this.vmMsgError( '领用失败！' );
          }
        })
      },
    },
    created(){
      this.getLoadData();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/test';
  @import '../../../../../style/style';
  div.g-container{padding:0;width:100%;}
  .g-tree_content .g-sectionR > header.g-liOneRow{border:none;}
</style>




