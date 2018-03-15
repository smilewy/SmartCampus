<template>
  <div class="g-container">
    <header class="g-textHeader g-liOneRow">
      <h2>审批设置</h2>
      <el-button class="radiusButton" type="primary" @click="saveClick">保存</el-button>
    </header>
    <section class="g-threePart g-liOneRow">
      <div class="g-sectionL">
        <header class="gL-header">
          <h2>选择资产分类 <small style="color:red; font-size: 12px">（需要在资产分类设置使用该分类才会显示）</small></h2>
          <el-input @input="fuzzySortClick" v-model="fuzzySortInput" class="fuzzyInput" placeholder="请输入资产分类"
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
              ref="sortTree" 
              :filter-node-method="filterSortNode" 
              @current-change="handleNodeClick">
          </el-tree>
        </section>
      </div>
      <div class="g-sectionC">
        <header class="g-textHeader">
          <span>是否需审批:</span>
          <el-switch v-model="isChecked" active-text="是" inactive-text="否" active-color="#13b5b1" inactive-color=""></el-switch>
        </header>
        <section>
          <p>领用/出库审批人</p>
          <ul class="g-contentContainer g-flexStartWrapRow">
            <li v-for="(content,index) in assetsPersonData" class="g-liOneRow"><span v-text="content.name"></span><i
              class="el-icon-close selfCenter" @click="setAssetsType(index)"></i></li>
          </ul>
        </section>
      </div>
      <div class="g-sectionL g-sectionL-R">
        <header class="gL-header">
          <h2>选择审批人</h2>
          <el-input @input="fuzzyPersonClick" v-model="fuzzyPersonInput" class="fuzzyInput" placeholder="请输入审批人姓名"
                    suffix-icon="el-icon-search"></el-input>
        </header>
        <section class="gL-section">
          <el-tree 
              v-loading="loading1" 
              element-loading-text="拼命加载中" 
              element-loading-spinner="el-icon-loading" 
              @node-click="checkChange" 
              :highlight-current="false" 
              node-key="id"
              :data="treePersonData" 
              :props="defaultPersonProps" 
              ref="personTree"
              :filter-node-method="filterPersonNode">
          </el-tree>
        </section>
      </div>
    </section>
  </div>
</template>
<script>
  import {
    approvalSettingType,//选择资产分类
    approvalSettingPersonChoose,//选择审批人
    approvalSettingSave,//保存
    approvalSettingGetPerson,//得到审批人
  } from '@/api/http'
  import {handlerAjaxData} from '@/assets/js/common'

  export default {
    data() {
      let _self = this;
      return {
        /*选择资产分类模糊查询*/
        fuzzySortInput: '',
        /*选择审批人模糊查询*/
        fuzzyPersonInput: '',
        /*是否需审批*/
        isChecked: false,
        /*左边tree树*/
        treeData: [],
        defaultProps: {
          children: 'childs',
          label: 'assetsTypeName'
        },
        /*右边选择审批人*/
        isAssets: false,//是否选择资产分类
        treePersonData: [],
        defaultPersonProps: {
          children: 'data',
          label: 'name',
          disabled: false
        },
        /*中间table框*/
        assetsPersonData: [],//领用/出库审批人绑定在页面上的数据
        /*send ajax params*/
        assetsTypeId: '',//选中资产分类的id
        loading: false,
        loading1: false
      }
    },
    watch: {
      assetsTypeId(newValue) {
        if (newValue) {
          this.isAssets = true;
        }
      },
    },
    methods: {
      /*选择资产分类模糊查询*/
      fuzzySortClick() {
        /*input框输入值改变触发的函数*/
        this.$refs['sortTree'].filter(this.fuzzySortInput);
      },
      filterSortNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.assetsTypeName.indexOf(value) !== -1;
      },
      /*选择审批人模糊查询*/
      fuzzyPersonClick() {
        this.$refs['personTree'].filter(this.fuzzyPersonInput);
      },
      filterPersonNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.name.indexOf(value) !== -1;
      },
      /*删除领用/出库审批人*/
      setAssetsType(ix) {
        this.assetsPersonData.splice(ix,1);
      },
      //选择审批人
      checkChange(data) {
        if(data.id&&this.isChecked){
          if(!this.assetsTypeId){
            this.vmMsgWarning( '请选择你需设置的资产分类！' );
            return false;
          }
          for(let obj of this.assetsPersonData){
            if(obj.id==data.id){
              this.vmMsgWarning( '你已添加过该人员！' );
              return false;
            }
          }
          this.assetsPersonData.push(data);
        }
      },
      //选择资产分类
      handleNodeClick(data) {
        this.assetsTypeId = data.assetsTypeId;
        this.getAssetsPersonAjax();
      },
      /*send ajax---------*/
      sendDataLoad() {
        this.loading = true;
        this.loading1 = true;
        approvalSettingType().then(data => {
          this.loading = false;
          /*得到资产分类*/
          this.treeData = handlerAjaxData(data) || [];
        });
        approvalSettingPersonChoose().then(data => {
          this.loading1 = false;
          /*得到选择审批人*/
          this.treePersonData = handlerAjaxData(data) || [];
        });
      },
      getAssetsPersonAjax() {
        approvalSettingGetPerson({assetsTypeId: this.assetsTypeId}).then(data => {
          let personData = handlerAjaxData(data);
          this.assetsPersonData=personData;
        })
      },
      saveClick() {
        let idParams = [];
        if (this.assetsTypeId) {
          this.assetsPersonData.forEach((value, index) => {
            if (Object.keys(value).includes('id')) {
              idParams.push(value.id);
            }
          });
          approvalSettingSave({
            assetsTypeId: this.assetsTypeId,
            id: idParams,
            ifApprov: Number(this.isChecked)
          }).then(data => {
            if (data.statu) {
              this.vmMsgSuccess( '保存成功！' );
            } else {
              this.vmMsgError( data.message );
            }
          });
        }
        else {
          this.vmMsgWarning( '请选择你需设置的资产分类' ); 
          return false;
        }
      },
    },
    created() {
      this.sendDataLoad();
    },
  }
</script>
<style lang="less" scoped>
  /*@import '../../../../style/test';*/
  @import '../../../../style/style';

  .g-container .g-threePart .g-sectionC {
    .width(718, 1582);
    margin-left: 0;
  }

  .g-container .g-threePart .g-sectionL-R {
    margin-right: 0;
  }

  .g-container .g-threePart .g-sectionL {
    .width(412, 1582);
  }

  .g-sectionC .g-textHeader {
    padding: 20/16rem;
  }

  .g-sectionC section {
    padding: 0 20/16rem;
    p {
      .fontSize(20);
      .marginBottom(20);
    }
  }

  .g-contentContainer { /*678*/
    width: 100%;
    li {
      .width(80, 678);
      padding: 8/16rem 10/16rem;
      position: relative;
      background: @liColor;
      .border-radius(4/16rem);
      margin-right: 20/16rem;
      margin-bottom: 20/16rem;
      color: #fff;
      .fontSize(14);
      i {
        .fontSize(14);
        color: #fff;
        &:hover {
          cursor: pointer;
        }
      }
    }
  }
</style>


