<template>
  <div class="g-hasPadding g-container">
    <header class="g-textHeader g-flexStartRow">
      <div class="g-headerButtonGroup">
        <h2>资产明细</h2>
      </div>
    </header>
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
        <header class="g-liOneRow g-sa_header_search">
          <div class="gs-button alertsBtn">
            <el-button-group>
              <el-button class="filt buttonChild" @click="exportClick" title="导出">
                <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
                <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
              </el-button>
              <el-button class="filt buttonChild" title="打印预览" @click="operationData('print')">
                <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
                <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
              </el-button>
              <el-button class="filt buttonChild" title="删除" @click="handlerClick('delete')">
                <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete.png" />
                <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete_highlight.png" />
              </el-button>
            </el-button-group>
          </div>
          <div class="gs-refresh g-fuzzyInput">
            <el-input type="text" v-model="fuzzyAjaxInput" suffix-icon="el-icon-search" placeholder="请输入需要查询的值" @change="fuzzyAjax"></el-input>
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
          <el-table-column label="分类代码" min-width="120" prop="assetsTypeId" sortable="custom"></el-table-column>
          <el-table-column label="存放位置" min-width="150" prop="storageLocation" sortable="custom"></el-table-column>
          <el-table-column label="供应商" min-width="120" prop="supplier" sortable="custom"></el-table-column>
          <el-table-column label="单位" min-width="80" prop="unit" sortable="custom"></el-table-column>
          <el-table-column label="品牌型号" min-width="150" prop="brandModel" sortable="custom"></el-table-column>
          <el-table-column label="规格" min-width="120" prop="spec" sortable="custom"></el-table-column>
          <el-table-column label="单价（元）" min-width="120" prop="onePrice" sortable="custom"></el-table-column>
          <el-table-column label="备注" min-width="150" prop="remarks" sortable="custom"></el-table-column>
          <el-table-column label="领用状态" min-width="120" sortable="custom">
            <template slot-scope="prop">
              <span v-if="Number(prop.row.ifRecive)">已领用</span>
              <span v-else>未领用</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" fixed="right">
            <template slot-scope="props">
              <el-button type="text" @click="handlerClick('edit',props.$index)">编辑</el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <el-dialog class="g-tree_content" title="编辑" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
        <div class="g-dialogConContainer">
          <el-form :model="gradeDialogForm" :rules="formRules" ref="dialogForm" label-width="100px" label-position="right">
            <el-form-item label="资产名称:" prop="assetsName">
              <el-input v-model="gradeDialogForm.assetsName" placeholder="请输入资产名称"></el-input>
            </el-form-item>
            <el-form-item label="存放位置:" prop="storageLocation">
              <el-input v-model="gradeDialogForm.storageLocation" placeholder="请输入存放位置"></el-input>
            </el-form-item>
            <el-form-item label="打印次数:" prop="dyCount">
              <el-input v-model="gradeDialogForm.dyCount" placeholder="请输入打印次数"></el-input>
            </el-form-item>
            <el-form-item label="供应商:" prop="supplier">
              <el-input v-model="gradeDialogForm.supplier" placeholder="请输入供应商"></el-input>
            </el-form-item>
            <el-form-item label="单位:" prop="unit">
              <el-input v-model="gradeDialogForm.unit" placeholder="请输入单位"></el-input>
            </el-form-item>
            <el-form-item label="品牌型号:" prop="brandModel">
              <el-input v-model="gradeDialogForm.brandModel" placeholder="请输入品牌型号"></el-input>
            </el-form-item>
            <el-form-item label="规格:" prop="spec">
              <el-input v-model="gradeDialogForm.spec" placeholder="请输入规格"></el-input>
            </el-form-item>
            <el-form-item label="单价（元）:" prop="onePrice">
              <el-input v-model="gradeDialogForm.onePrice" placeholder="请输入单价（元）"></el-input>
            </el-form-item>
            <el-form-item label="备注:" prop="remarks">
              <el-input v-model="gradeDialogForm.remarks" placeholder="请输入备注"></el-input>
            </el-form-item>
            <el-form-item label="领用状态:">
              <el-switch active-text="已领用" inactive-text="未领用" v-model="gradeDialogForm.ifRecive"></el-switch>
            </el-form-item>
          </el-form>
        </div>
        <div class="g-footer">
          <el-button class="radiusButton" @click="confirmChange" type="primary">确定</el-button>
        </div>
      </el-dialog>
    </section>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {
    assetDetailLoad,//页面加载信息
    assetDetailType,//得到资产分类
    assetDetailSort,//排序
    assetDetailSearch,//模糊查询
    assetDetailChange,//修改信息
  } from '@/api/http'
  import {handlerAjaxData} from '@/assets/js/common'
  import req from '@/assets/js/common'
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
        /*资产分类模糊查询*/
        fuzzyInput:'',
        /*table表单*/
        newStoragetable:[],
        mutleData:[],
        fuzzyAjaxInput:'',
        /*tree*/
        treeData:[],
        defaultProps: {
          children:'childs',
          label:'assetsTypeName'
        },
        /*右边*/
        /*模糊查询*/
        fuzzyTableInput:'',
        /*弹框*/
        isDialog:false,
        gradeDialogForm:{
          assetsId:'',
          assetsName:'',
          type:'',//品牌型号
          storageLocation:'',//存放位置
          dyCount:null,//
          supplier:'',//
          unit:'',
          brandModel:'',
          spec:'',
          onePrice:'',
          remarks:'',
          ifRecive:''
        },
        formRules: {
          dyCount: [
            {required: true, validator: validateNum, trigger: 'blur'},
             {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
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
            {validator: validateNum, trigger: 'blur'},
            {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ],
          storageLocation: [
            {min: 1, max: 30, message: '长度在 1 到 30 个字符', trigger: 'blur'}
          ],
          remarks:[
            {min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur'}
          ]
        },
        /*ajax params*/
        assetsTypeId:'',
        loading: false,
        loading1: false
      }
    },
    methods:{
      operationData(type){
        let sAy = [], hdData = {
          assetsName: '资产名称',
          assetsNumber: '资产编号',
          assetsTypeId: '分类代码',
          storageLocation: '存放地址',
          dyCount: '打印次数',
          supplier: '供应商',
          unit: '单位',
          brandModel: '品牌型号',
          spec: '规格',
          onePrice: '单价',
          remarks: '备注',
          ifRecive: '领用状态'
        };
        sAy.push(hdData);
        for (let obj of this.newStoragetable) {
          let d = {};
          for (let name in hdData) {
            if( name == 'ifRecive' ){
              d[name] = obj[name] == 0 ? '未领用' : '已领用';
            } else {
              d[name] = obj[name] || '';
            }
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.messageManagement', sAy);
        } else {
          req.lodop(sAy);
        }
      },
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
        return data.techerName.indexOf(value) !== -1;
      },
      /*tree点击事件*/
      handleNodeClick(data,node){
        this.assetsTypeId=data.assetsTypeId;
        this.getTableLoad();
      },
      /*右边*/
      /*模糊查询*/
      fuzzyTableClick(){},
      handleSelectionChange(val){
          this.mutleData=val;
      },
      /*table*/
      handlerClick(type,index){
        if(type=='edit'){
          this.isDialog=true;
          Object.keys(this.gradeDialogForm).forEach((value)=>{
            this.gradeDialogForm[value]=this.newStoragetable[index][value];
          });
        }else{
            var self=this,data={
              assetsId:[]
            }, isAccepted = false;
            if(self.mutleData.length==0){
              this.vmMsgWarning( '请选择记录！' );
              return false;
            }
            for(let obj of self.mutleData){
              if(obj.ifRecive == 1){
                isAccepted = true; break;
              }
              data.assetsId.push(obj.assetsId);
              data.assetsId.push(obj.assetsId);
            }
          if( isAccepted ) { 
            this.vmMsgWarning( '无法删除，删除数据中存在已领用的资产！' );
            return;
          }
          self.$confirm('确定删除资产？', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
                req.ajaxSend('/school/assets/assetsList?type=delete','post',data,function (res) {
                  if(res.statu==1){
                    this.vmMsgSuccess( '删除成功！' );
                    self.getTableLoad();
                  }else{
                    this.vmMsgError( res.message );
                  }
                })

          }).catch(() => {
          });
        }
      },
      /*弹框*/
      handlerClose(done){
        done();
      },
      /*send ajax*/
      getLoadMsg(){
        this.loading = true;
        assetDetailType().then(data=>{
          this.loading = false;
          this.treeData=handlerAjaxData(data);
        })
      },
      getTableLoad(){
        this.loading1 = true;
        assetDetailLoad({assetsTypeId:this.assetsTypeId}).then(data=>{
          this.loading1 = false;
          this.newStoragetable=handlerAjaxData(data);
        });
      },
      sortChange(column,prop,order){
        this.loading1 = true;
        assetDetailSort({assetsTypeId:this.assetsTypeId,sort:column.prop,sortData:column.order}).then(data=>{
          this.loading1 = false;
          this.newStoragetable=handlerAjaxData(data);
        });
      },
      fuzzyAjax(){
        this.loading1 = true;
        assetDetailSearch({assetsTypeId:this.assetsTypeId,valueData:this.fuzzyAjaxInput}).then(data=>{
          this.loading1 = false;
          this.newStoragetable=handlerAjaxData(data);
        });
      },
      exportClick(){
          if(!this.assetsTypeId){
              return false;
          }
        req.downloadFile('.g-container','/school/assets/assetsList?type=export&assetsTypeId='+this.assetsTypeId,'post')
      },
      confirmChange(){
        this.$refs['dialogForm'].validate( valid => {
          if(valid){
            this.gradeDialogForm.ifRecive=Number(this.gradeDialogForm.ifRecive);
            assetDetailChange({data:this.gradeDialogForm}).then(data=>{
              if(data.statu == 1){
                this.vmMsgSuccess( '修改成功！' );
                this.isDialog=false;
                this.getTableLoad();
              }else{
                this.vmMsgError( data.message );
              }
            });
          } else {
            this.vmMsgError( '请将数据填写正确！' );
          }
        });
        
      },
    },
    created(){
      this.getLoadMsg();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/test';
  @import '../../../../style/style';
  .g-tree_content .g-sectionR > header.g-liOneRow{border:none;}

  /*弹框*/
  .g-tree_content h2{.fontSize(12);color:@HColor;padding-bottom:20/16rem;}
  .g-tree_content .el-dialog--tiny{.NotLineheight(560);}
  /*容器*/
  .g-dialogConContainer{width:100%;.NotLineheight(405);overflow-x:hidden;overflow-y:auto;}
  .g-tree_content ul{
    width:100%;border-top:1px solid @borderColor;border-bottom:1px solid @borderColor;.marginBottom(24);
  }
  /*行*/
  .g-tree_content .g-dialogRow{
    width:100%;
    &:not(:last-of-type){border-bottom:1px solid @borderColor;}
  }
  /*列*/
  .g-tree_content .g-dialogRow>div{
    text-align:center;.fontSize(14);color:@normalColor;padding:15/16rem 0;
    &:nth-of-type(odd){width:35.7%;border-right:1px solid @borderColor;}
    &:nth-of-type(even){width:64.3%;}
  }

  .g-contentOne_header{.widthRem(100);.height(30);margin-bottom:25/16rem;font-size:14/16rem;color:#fff;background:@buttonActive;.box-shadow(0 4/16rem 6/16rem 0 rgba(0,0,0,.2));text-align:center;.border-bottom-right-radius(15/16rem);.border-top-right-radius(15/16rem);}
  /*审批结果处css*/
  .g-chooseButton{/*1582*/
    width:100%;padding:0 0 30/16rem 60/16rem;.fontSize(14);color:@normalColor;
    span{margin-right:20/16rem;}
    div{.widthRem(80);.height(30);text-align:center;.box-sizing();
      &:hover{cursor:pointer;}
      &:first-of-type{.border-bottom-left-radius(15/16rem);.border-top-left-radius(15/16rem);}
      &:last-of-type{.border-top-right-radius(15/16rem);.border-bottom-right-radius(15/16rem);}
    }
    div.activeCss{background:@green;color:#fff;border:none;}
    div.normalCss{color:@normalColor;border:1px solid @borderColor;}
  }
  .g-footer{width:100%;padding:24/16rem 0;text-align:center;}
</style>




