<template>
  <ul class="navBarMenu">
    <li class="bar_li"
        :class="{'active_one':navData.level=='2'&&navData.ifHave==1,'active_two':navData.level=='3'&&navData.ifHave==1,'active_three':navData.level=='4'&&navData.ifHave==1,'active_four':(navData.level=='5'||(navData.level=='4'&&!navData.childs))&&navData.ifHave==1}"
        v-for='(navData,ix) in data' :key='navData.modelId' @click.stop='jumpPage(navData)'>
      <el-row type='flex' align="middle" class="bar_li_content">
        <el-col :span="24" v-if="!navData.childs&&!navData.logoUrl">
          <img src="../../static/navBarImg/icon_list_style.png" alt="" class="listImg" v-if="navData.level=='4'"><span>{{navData.modelName}}</span>
        </el-col>
        <el-col :span="16" v-if="navData.childs||navData.logoUrl">
          <img src="../../static/navBarImg/icon_list_style.png" alt="" class="listImg" v-if="navData.level=='4'"><span>{{navData.modelName}}</span>
        </el-col>
        <el-col :span="8" class="iconOrImg" v-if="navData.childs||navData.logoUrl">
          <el-row type="flex" align="middle">
            <span class="gap_logo">
              <i class="el-icon-arrow-down" v-show="navData.ifHave==0&&navData.childs"></i>
            <i class="el-icon-arrow-up" v-show="navData.ifHave==1&&navData.childs"></i>
            </span>
            <img :src="'./static/navBarImg/'+navData.logoUrl" alt=""
                 v-if="(navData.urlImg&&navData.ifHave!=1)||(!navData.urlImg&&navData.logoUrl)"/>
            <img :src="'./static/navBarImg/'+navData.urlImg" alt="" v-if="navData.urlImg&&navData.ifHave==1"/>
          </el-row>
        </el-col>
      </el-row>
      <el-row v-if="navData.childs" class="bar_li_child">
        <navTree :data='navData.childs' @setJumpPage='jumpPage' v-show='navData.ifHave==1'/>
      </el-row>
    </li>
  </ul>
</template>

<script>
  export default {
    name: 'navTree',
    props: ['data'],
    data() {
      return {}
    },
    computed: {},
    methods: {
      jumpPage(val) {
        this.$emit('setJumpPage', val);
      }
    }
  }
</script>

<style>
  .navBarMenu li {
    padding: .5rem 0;
  }

  .navBarMenu .bar_li_content {
    padding: .5rem 0;
    cursor: pointer;
  }

  .navBarMenu .bar_li_child {
    padding: 0 .5rem;
  }

  .navBarMenu .bar_li.active_one > .bar_li_content {
    background: #30b3fd; /* 一些不支持背景渐变的浏览器 */
    background: -webkit-linear-gradient(left, #2f91f9, #30c7ff); /* Safari 5.1 - 6.0 */
    background: -o-linear-gradient(right, #2f91f9, #30c7ff); /* Opera 11.1 - 12.0 */
    background: -moz-linear-gradient(right, #2f91f9, #30c7ff); /* Firefox 3.6 - 15 */
    background: linear-gradient(to right, #2f91f9, #30c7ff); /* 标准的语法 */
    color: #fff;
    border-radius: 4px 4px 0 0;
  }

  .navBarMenu .bar_li.active_one > .bar_li_child {
    background-color: #f4f4f4;
    color: #2c3e50;
    border-radius: 0 0 4px 4px;
  }

  .navBarMenu .bar_li.active_three > .bar_li_child {
    background-color: #e2e2e2;
  }

  .navBarMenu .bar_li.active_four > .bar_li_content {
    color: #489af9;
  }

  .navBarMenu .iconOrImg img {
    width: 1.2rem;
    margin-left: .5rem;
  }

  .navBarMenu .gap_logo {
    display: inline-block;
    width: .875rem;
    font-size: .875rem;
  }

  .navBarMenu .listImg {
    margin-right: .5rem;
  }
</style>
